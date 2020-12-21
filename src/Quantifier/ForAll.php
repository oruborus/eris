<?php

namespace Eris\Quantifier;

use BadMethodCallException;
use Exception;
use RuntimeException;
use Throwable;
use Eris\Antecedent\IndependentConstraintsAntecedent;
use Eris\Antecedent\SingleCallbackAntecedent;
use Eris\Contracts\Antecedent;
use Eris\Contracts\Generator;
use Eris\Contracts\Listener;
use Eris\Contracts\Growth;
use Eris\Contracts\TerminationCondition;
use Eris\Generator\ConstantGenerator;
use Eris\Generator\SkipValueException;
use Eris\Shrinker\ShrinkerFactory;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use PHPUnit\Framework\Constraint\Constraint;

class ForAll
{
    const DEFAULT_MAX_SIZE = 200;

    /**
     * @var Generator[] $generators
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
     * @var array<string, string> $aliases
     */
    private array $aliases = [
        'and' => 'when',
        'then' => '__invoke',
    ];

    public function __construct(
        array $generators,
        Growth $growth,
        ShrinkerFactory $shrinkerFactory,
        string $shrinkerFactoryMethod,
        RandomRange $rand
    ) {
        $this->generators = $this->generatorsFrom($generators);
        $this->shrinkerFactory = $shrinkerFactory;
        $this->shrinkerFactoryMethod = $shrinkerFactoryMethod;
        $this->rand = $rand;

        $this->growth = $growth;
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
     * Examples of calls:
     * when($constraint1, $constraint2, ..., $constraintN)
     * when(callable $takesNArguments)
     * when(Antecedent $antecedent)
     */
    public function when(/* see docblock */): self
    {
        $arguments = func_get_args();
        if ($arguments[0] instanceof Antecedent) {
            $antecedent = $arguments[0];
        } elseif ($arguments[0] instanceof Constraint) {
            $antecedent = IndependentConstraintsAntecedent::fromAll($arguments);
        } elseif ($arguments && count($arguments) == 1) {
            $antecedent = SingleCallbackAntecedent::from($arguments[0]);
        } else {
            throw new \InvalidArgumentException("Invalid call to when(): " . var_export($arguments, true));
        }
        $this->antecedents[] = $antecedent;
        return $this;
    }

    /**
     * @param callable $assertion
     */
    public function __invoke($assertion)
    {
        $sizes = $this->growth;
        $redTestException = null;
        $values = [];
        try {
            $this->notifyListeners('startPropertyVerification');
            for (
                $iteration = 0;
                $iteration < $this->getIterations()
                    && !$this->terminationConditionsAreSatisfied();
                $iteration++
            ) {
                $generatedValues = [];
                $values = [];
                try {
                    foreach ($this->generators as $name => $generator) {
                        $value = $generator($sizes[$iteration], $this->rand);
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

    /**
     * @see $this->aliases
     * @method then($assertion)
     * @method implies($assertion)
     * @method imply($assertion)
     */
    public function __call(string $method, array $arguments)
    {
        if (isset($this->aliases[$method])) {
            return call_user_func_array(
                [$this, $this->aliases[$method]],
                $arguments
            );
        }
        throw new BadMethodCallException("Method " . __CLASS__ . "::{$method} does not exist");
    }

    /**
     * @return Generator[]
     *
     * @psalm-return list<Generator>
     */
    private function generatorsFrom(array $supposedToBeGenerators): array
    {
        $generators = [];
        foreach ($supposedToBeGenerators as $supposedToBeGenerator) {
            if (!$supposedToBeGenerator instanceof Generator) {
                $generators[] = new ConstantGenerator($supposedToBeGenerator);
            } else {
                $generators[] = $supposedToBeGenerator;
            }
        }
        return $generators;
    }

    private function notifyListeners(/*$event, [$parameterA[, $parameterB[, ...]]]*/): void
    {
        $arguments = func_get_args();
        $event = array_shift($arguments);
        foreach ($this->listeners as $listener) {
            call_user_func_array(
                [$listener, $event],
                $arguments
            );
        }
    }

    private function antecedentsAreSatisfied(array $values): bool
    {
        foreach ($this->antecedents as $antecedentToVerify) {
            if (!call_user_func(
                [$antecedentToVerify, 'evaluate'],
                $values
            )) {
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
