<?php

declare(strict_types=1);

namespace Eris\Quantifier;

use DateInterval;
use Eris\Antecedent\IndependentConstraintsAntecedent;
use Eris\Antecedent\SingleCallbackAntecedent;
use Eris\Contracts\Antecedent;
use Eris\Contracts\Generator;
use Eris\Contracts\Growth;
use Eris\Contracts\Listener;
use Eris\Contracts\Quantifier;
use Eris\Contracts\Source;
use Eris\Contracts\TerminationCondition;
use Eris\Generator\SkipValueException;
use Eris\Growth\TriangularGrowth;
use Eris\Listener\TimeBasedTerminationCondition;
use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Shrinker\ShrinkerFactory;
use Eris\Value\Value;
use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\Constraint;
use RuntimeException;

use function call_user_func_array;
use function class_implements;
use function getenv;
use function in_array;
use function strtolower;
use function var_export;

class ForAll implements Quantifier
{
    public const DEFAULT_MAX_SIZE = 200;

    public const DEFAULT_MAX_ITERATIONS = 100;

    /**
     * @var list<Generator<mixed>> $generators
     */
    private array $generators;

    private int $maximumIterations = self::DEFAULT_MAX_ITERATIONS;

    private int $maximumSize = self::DEFAULT_MAX_SIZE;

    /**
     * @var class-string<Growth> $growthClass
     */
    private string $growthClass;

    /**
     * @var class-string<Source> $sourceClass
     */
    private string $sourceClass;

    private ShrinkerFactory $shrinkerFactory;

    /**
     * @var list<Antecedent> $antecedents
     */
    private array $antecedents = [];

    /**
     * @var list<TerminationCondition> $terminationConditions
     */
    private array $terminationConditions = [];

    /**
     * @var Listener[] $listeners
     */
    private array $listeners = [];

    private bool $shrinkingDisabled = false;

    /**
     * @param list<Generator<mixed>> $generators
     */
    public function __construct(array $generators)
    {
        $this->generators  = $generators;
        $this->growthClass = TriangularGrowth::class;
        $this->sourceClass = RandSource::class;
        $this->shrinkerFactory = new ShrinkerFactory();
    }

    public function limitTo(int|DateInterval $limit): self
    {
        if ($limit instanceof DateInterval) {
            $terminationCondition = new TimeBasedTerminationCondition('time', $limit);

            return $this->listenTo($terminationCondition)->stopOn($terminationCondition);
        }

        return $this->withMaximumIterations($limit);
    }

    public function listenTo(Listener $listener): self
    {
        $this->listeners[] = $listener;

        return $this;
    }

    /**
     * Alias of Eris\Quantifier\ForAll::hook
     */
    public function hook(Listener $listener): self
    {
        return $this->listenTo($listener);
    }

    public function stopOn(TerminationCondition $terminationCondition): self
    {
        $this->terminationConditions[] = $terminationCondition;

        return $this;
    }

    /**
     * @param string|class-string<Growth> $growth
     */
    public function withGrowth(string $growth): self
    {
        $interfaces = class_implements($growth, true);

        /**
         * @psalm-suppress PropertyTypeCoercion
         */
        if ($interfaces && in_array(Growth::class, $interfaces)) {
            $this->growthClass = $growth;

            return $this;
        }

        $growth = strtolower($growth);

        $growthClasses = [
            'linear'     => \Eris\Growth\LinearGrowth::class,
            'triangular' => \Eris\Growth\TriangularGrowth::class,
        ];

        if (in_array($growth, $growthClasses)) {
            $this->growthClass = $growthClasses[$growth];
        }

        return $this;
    }

    /**
     * Alias of Eris\Quantifier\ForAll::withMaximumSize
     */
    public function withMaxSize(int $maxSize): self
    {
        return $this->withMaximumSize($maxSize);
    }

    public function withMaximumSize(int $maximumSize): self
    {
        $this->maximumSize = $maximumSize;

        return $this;
    }

    public function getMaximumSize(): int
    {
        return $this->maximumSize;
    }

    public function withMaximumIterations(int $maximumIterations): self
    {
        $this->maximumIterations = $maximumIterations;

        return $this;
    }

    public function getMaximumIterations(): int
    {
        return $this->maximumIterations;
    }

    /**
     * Alias of Eris\Quantifier\ForAll::withoutShrinking
     */
    public function disableShrinking(): self
    {
        return $this->withoutShrinking();
    }

    public function withoutShrinking(): self
    {
        $this->shrinkingDisabled = true;

        return $this;
    }

    /**
     * @param string|class-string<Source> $source
     */
    public function withRand(string $source): self
    {
        $interfaces = class_implements($source, true);

        /**
         * @psalm-suppress PropertyTypeCoercion
         */
        if ($interfaces && in_array(Source::class, $interfaces)) {
            $this->sourceClass = $source;

            return $this;
        }

        $source = strtolower($source);

        $sourceClasses = [
            'rand'     => \Eris\Random\RandSource::class,
        ];

        if (in_array($source, $sourceClasses)) {
            $this->sourceClass = $sourceClasses[$source];
        }

        return $this;
    }

    public function withShrinkingTimeLimit(?int $shrinkingTimeLimit): self
    {
        $this->shrinkerFactory = new ShrinkerFactory($shrinkingTimeLimit);

        return $this;
    }

    /**
     * @param Antecedent|Constraint|callable(mixed...):bool $firstArgument
     */
    public function when($firstArgument, Constraint ...$arguments): self
    {
        if ($firstArgument instanceof Constraint) {
            $this->antecedents[] = new IndependentConstraintsAntecedent([$firstArgument] + $arguments);
            return $this;
        }

        if ($firstArgument instanceof Antecedent) {
            $this->antecedents[] = $firstArgument;
            return $this;
        }

        $this->antecedents[] = new SingleCallbackAntecedent($firstArgument);

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
        $growth              = new ($this->growthClass)($this->maximumSize, $this->maximumIterations);
        $range               = new RandomRange(new ($this->sourceClass)());
        $ordinaryEvaluations = 0;
        $redTestException    = null;
        $values              = null;

        $this->notifyListeners('startPropertyVerification');

        try {
            for (
                $iteration = 0;
                $iteration < $this->maximumIterations && !$this->terminationConditionsAreSatisfied();
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
                        $value = $generator($growth[$iteration], $range);
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

                $ordinaryEvaluations++;

                try {
                    $assertion(...$generation->value());
                } catch (AssertionFailedError $exception) {
                    $this->notifyListeners('failure', $generation->value(), $exception);

                    if ($this->shrinkingDisabled) {
                        throw $exception;
                    }

                    $shrinker = $this->shrinkerFactory->multiple($this->generators, $assertion);

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
                throw new RuntimeException(
                    "Original input: " . var_export($values, true) . PHP_EOL . "Possibly shrunk input follows." . PHP_EOL,
                    -1,
                    $e
                );
            }

            throw $redTestException;
        } finally {
            $this->notifyListeners(
                'endPropertyVerification',
                $ordinaryEvaluations,
                $this->maximumIterations,
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
