<?php

declare(strict_types=1);

namespace Eris\Quantifier;

use Eris\Antecedent\IndependentConstraintsAntecedent;
use Eris\Antecedent\SingleCallbackAntecedent;
use Eris\Contracts\Antecedent;
use Eris\Contracts\Generator;
use Eris\Contracts\Listener;
use Eris\Contracts\Growth;
use Eris\Contracts\TerminationCondition;
use Eris\Generator\SkipValueException;
use Eris\Shrinker\ShrinkerFactory;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Exception;
use PHPUnit\Framework\Constraint\Constraint;
use RuntimeException;
use Throwable;

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

    private ShrinkerFactory $shrinkerFactory;

    private string $shrinkerFactoryMethod;

    private RandomRange $rand;

    private array $antecedents = [];

    private int $ordinaryEvaluations = 0;

    private array $terminationConditions = [];

    /**
     * @var Listener[] $listeners
     */
    private array $listeners = [];

    private bool $shrinkingEnabled = true;

    /**
     * @param list<Generator> $generators
     */
    public function __construct(
        array $generators,
        Growth $growth,
        ShrinkerFactory $shrinkerFactory,
        string $shrinkerFactoryMethod,
        RandomRange $rand
    ) {
        $this->generators            = $generators;
        $this->growth                = $growth;
        $this->shrinkerFactory       = $shrinkerFactory;
        $this->shrinkerFactoryMethod = $shrinkerFactoryMethod;
        $this->rand                  = $rand;
    }

    public function withMaxSize(int $maxSize): self
    {
        $this->growth = new $this->growth($maxSize, $this->growth->count());
        return $this;
    }

    public function getMaxSize(): int
    {
        return $this->growth->getMaximumSize();
    }

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
     * @param Antecent|Constraint|callable $firstArgument
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
     * @param Antecent|Constraint|callable $firstArgument
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
        $values = [];
        try {
            for (
                $iteration = 0;
                $iteration < $this->getIterations() && !$this->terminationConditionsAreSatisfied();
                $iteration++
            ) {
                $generatedValues = [];
                $values = [];

                try {
                    foreach ($this->generators as $generator) {
                        $value = $generator($this->growth[$iteration], $this->rand);
                        $generatedValues[] = $value;
                        $values[] = $value->unbox();
                    }
                } catch (SkipValueException $e) {
                    continue;
                }

                $generation = new Value($values, $generatedValues);

                $this->notifyListeners('newGeneration', $generation->unbox(), $iteration);

                if (!$this->antecedentsAreSatisfied($values)) {
                    continue;
                }

                $this->ordinaryEvaluations++;

                Evaluation::of($assertion)
                    // TODO: coupling between here and the TupleGenerator used inside?
                    ->with($generation)
                    ->onFailure(function (Value $generatedValues, Throwable $exception) use ($assertion): void {
                        $this->notifyListeners('failure', $generatedValues->unbox(), $exception);

                        if (!$this->shrinkingEnabled) {
                            throw $exception;
                        }

                        $shrinkerFactoryMethod = $this->shrinkerFactoryMethod;
                        $shrinking = $this->shrinkerFactory->$shrinkerFactoryMethod($this->generators, $assertion);

                        // MAYBE: put into ShrinkerFactory?
                        $shrinking
                            ->addGoodShrinkCondition(function (Value $generatedValues) {
                                return $this->antecedentsAreSatisfied($generatedValues->unbox());
                            })
                            ->onAttempt(function (Value $generatedValues) {
                                $this->notifyListeners('shrinking', $generatedValues->unbox());
                            })
                            ->from($generatedValues, $exception);
                    })
                    ->execute();
            }
        } catch (Exception $e) {
            $redTestException = $e;

            if ((bool) getenv('ERIS_ORIGINAL_INPUT')) {
                $message = "Original input: " . var_export($values, true) . PHP_EOL
                    . "Possibly shrinked input follows." . PHP_EOL;
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
