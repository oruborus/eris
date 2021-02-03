<?php

declare(strict_types=1);

namespace Eris\Quantifier;

use Eris\Antecedent\IndependentConstraintsAntecedent;
use Eris\Antecedent\SingleCallbackAntecedent;
use Eris\Contracts\Antecedent;
use Eris\Contracts\Generator;
use Eris\Contracts\Growth;
use Eris\Contracts\Listener;
use Eris\Contracts\Shrinker;
use Eris\Contracts\TerminationCondition;
use Eris\Generator\SkipValueException;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\Constraint;
use RuntimeException;

use function call_user_func_array;
use function get_class;
use function getenv;
use function var_export;

class ForAll
{
    public const DEFAULT_MAX_SIZE = 200;

    /**
     * @var list<Generator<mixed>> $generators
     */
    private array $generators;

    private Growth $growth;

    /**
     * @var callable(list<Generator<mixed>>, callable(mixed...):void):Shrinker $shrinkerFactoryFunction
     */
    private $shrinkerFactoryFunction;

    private RandomRange $rand;

    /**
     * @var list<Antecedent> $antecedents
     */
    private array $antecedents = [];

    private int $ordinaryEvaluations = 0;

    /**
     * @var list<TerminationCondition> $terminationConditions
     */
    private array $terminationConditions = [];

    /**
     * @var Listener[] $listeners
     */
    private array $listeners = [];

    private bool $shrinkingEnabled = true;

    /**
     * @param list<Generator<mixed>> $generators
     * @param callable(list<Generator<mixed>>, callable(mixed...):void):Shrinker $shrinkerFactoryFunction
     */
    public function __construct(
        array $generators,
        Growth $growth,
        $shrinkerFactoryFunction,
        RandomRange $rand
    ) {
        $this->generators              = $generators;
        $this->growth                  = $growth;
        $this->shrinkerFactoryFunction = $shrinkerFactoryFunction;
        $this->rand                    = $rand;
    }

    /**
     * @psalm-suppress UndefinedClass
     * @psalm-suppress PropertyTypeCoercion
     */
    public function withMaxSize(int $maxSize): self
    {
        $growthClass = get_class($this->growth);
        $this->growth = new $growthClass($maxSize, $this->growth->count());
        return $this;
    }

    public function getMaxSize(): int
    {
        return $this->growth->getMaximumSize();
    }

    /**
     * @psalm-suppress UndefinedClass
     * @psalm-suppress PropertyTypeCoercion
     */
    public function withIterations(int $iterations): self
    {
        $growthClass = get_class($this->growth);
        $this->growth = new $growthClass($this->growth->getMaximumSize(), $iterations);

        return $this;
    }

    public function getIterations(): int
    {
        return $this->growth->count();
    }

    public function hook(Listener $listener): static
    {
        $this->listeners[] = $listener;
        return $this;
    }

    public function stopOn(TerminationCondition $terminationCondition): static
    {
        $this->terminationConditions[] = $terminationCondition;
        return $this;
    }

    public function disableShrinking(): self
    {
        $this->shrinkingEnabled = false;
        return $this;
    }

    /**
     * @param Antecedent|Constraint|callable(mixed...):bool $firstArgument
     */
    public function and($firstArgument, Constraint ...$arguments): self
    {
        return $this->when($firstArgument, ...$arguments);
    }

    /**
     * Examples of calls:
     * when($constraint1, $constraint2, ..., $constraintN)
     * when(callable $takesNArguments)
     * when(Antecedent $antecedent)
     *
     * @param Antecedent|Constraint|callable(mixed...):bool $firstArgument
     */
    public function when($firstArgument, Constraint ...$arguments): static
    {
        if ($firstArgument instanceof Constraint) {
            $this->antecedents[] = new IndependentConstraintsAntecedent([$firstArgument] + $arguments);
            return $this;
        }

        if ($firstArgument instanceof Antecedent) {
            $this->antecedents[] = $firstArgument;
            return $this;
        }

        $this->antecedents[] = SingleCallbackAntecedent::from($firstArgument);

        return $this;
    }

    /**
     * @param callable(mixed...):void $assertion
     */
    public function then($assertion): void
    {
        $this->__invoke($assertion);
    }

    /**
     * @param callable(mixed...):void $assertion
     */
    public function __invoke($assertion): void
    {
        $this->notifyListeners('startPropertyVerification');

        $redTestException = null;

        try {
            for (
                $iteration = 0;
                $iteration < $this->getIterations() && !$this->terminationConditionsAreSatisfied();
                $iteration++
            ) {
                /**
                 * @var list<mixed> $values
                 */
                $values = [];
                $generatedValues = [];

                try {
                    /**
                     * @psalm-suppress PossiblyNullArgument
                     * @psalm-suppress MixedAssignment
                     */
                    foreach ($this->generators as $generator) {
                        $value = $generator($this->growth[$iteration], $this->rand);
                        $values[] = $value->value();
                        $generatedValues[] = $value;
                    }
                } catch (SkipValueException $e) {
                    continue;
                }

                $generation = new Value($values, $generatedValues);

                $this->notifyListeners('newGeneration', $generation->value(), $iteration);

                if (!$this->antecedentsAreSatisfied($values)) {
                    continue;
                }

                $this->ordinaryEvaluations++;

                try {
                    call_user_func_array($assertion, $generation->value());
                } catch (AssertionFailedError $exception) {
                    $this->notifyListeners('failure', $generation->value(), $exception);

                    if (!$this->shrinkingEnabled) {
                        throw $exception;
                    }

                    $shrinker = ($this->shrinkerFactoryFunction)($this->generators, $assertion);

                    /**
                     * TODO: MAYBE: put into ShrinkerFactory?
                     *
                     * @psalm-suppress MixedArgumentTypeCoercion
                     */
                    $shrinker
                        ->addGoodShrinkCondition(
                            /**
                             * @param Value<array> $generation
                             */
                            fn (Value $generation): bool => $this->antecedentsAreSatisfied($generation->value())
                        )
                        ->onAttempt(
                            /**
                             * @param Value<array> $generation
                             */
                            function (Value $generation): void {
                                $this->notifyListeners('shrinking', $generation->value());
                            }
                        )
                        ->from($generation, $exception);
                }
            }
        } catch (Exception $e) {
            $redTestException = $e;

            if ((bool) getenv('ERIS_ORIGINAL_INPUT')) {
                $message = "Original input: " . var_export($values ?? null, true) . PHP_EOL .
                    "Possibly shrinked input follows." . PHP_EOL;
                throw new RuntimeException($message, -1, $e);
            }

            throw $redTestException;
        } finally {
            $this->notifyListeners(
                'endPropertyVerification',
                $this->ordinaryEvaluations,
                $this->getIterations(),
                $redTestException
            );
        }
    }

    /**
     * @param mixed $arguments
     */
    private function notifyListeners(string $event, ...$arguments): void
    {
        foreach ($this->listeners as $listener) {
            $listener->{$event}(...$arguments);
        }
    }

    /**
     * @param array<mixed> $values
     */
    private function antecedentsAreSatisfied(array $values): bool
    {
        foreach ($this->antecedents as $antecedentToVerify) {
            if (!$antecedentToVerify->evaluate($values)) {
                return false;
            }
        }

        return true;
    }

    private function terminationConditionsAreSatisfied(): bool
    {
        foreach ($this->terminationConditions as $terminationCondition) {
            if ($terminationCondition->shouldTerminate()) {
                return true;
            }
        }

        return false;
    }
}
