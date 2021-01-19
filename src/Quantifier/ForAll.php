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

use function array_merge;
use function call_user_func;
use function call_user_func_array;
use function getenv;
use function var_export;

class ForAll
{
    const DEFAULT_MAX_SIZE = 200;

    /**
     * @var list<Generator> $generators
     */
    private array $generators;

    private Growth $growth;

    /**
     * @var callable(list<Generator>, callable):Shrinker $shrinkerFactoryFunction
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
     * @param list<Generator> $generators
     * @param callable(list<Generator>, callable):Shrinker $shrinkerFactoryFunction
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
        $this->growth = new $this->growth($maxSize, $this->growth->count());
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
        $this->growth = new $this->growth($this->growth->getMaximumSize(), $iterations);

        return $this;
    }

    public function getIterations(): int
    {
        return $this->growth->count();
    }

    public function hook(Listener $listener): self
    {
        $this->listeners[] = $listener;
        return $this;
    }

    public function stopOn(TerminationCondition $terminationCondition): self
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
     * @param Antecedent|Constraint|callable $firstArgument
     * @param list<Constraint> $arguments
     */
    public function and($firstArgument, ...$arguments): self
    {
        return $this->when($firstArgument, ...$arguments);
    }

    /**
     * Examples of calls:
     * when($constraint1, $constraint2, ..., $constraintN)
     * when(callable $takesNArguments)
     * when(Antecedent $antecedent)
     *
     * @param Antecedent|Constraint|callable $firstArgument
     * @param list<Constraint> $arguments
     */
    public function when($firstArgument, ...$arguments): self
    {
        if ($firstArgument instanceof Constraint) {
            $this->antecedents[] = IndependentConstraintsAntecedent::fromAll(array_merge([$firstArgument], $arguments));
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
     * @param callable $assertion
     */
    public function then(...$assertion): void
    {
        $this->__invoke(...$assertion);
    }

    /**
     * @param callable $assertion
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

                    // MAYBE: put into ShrinkerFactory?
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
            call_user_func_array([$listener, $event], $arguments);
        }
    }

    private function antecedentsAreSatisfied(array $values): bool
    {
        foreach ($this->antecedents as $antecedentToVerify) {
            if (!call_user_func([$antecedentToVerify, 'evaluate'], $values)) {
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
