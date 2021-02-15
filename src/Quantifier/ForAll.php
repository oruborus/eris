<?php

declare(strict_types=1);

namespace Eris\Quantifier;

use DateInterval;
use Eris\Antecedent\AntecedentCollection;
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
use Eris\Listener\ListenerCollection;
use Eris\Listener\MinimumEvaluations;
use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Shrinker\ShrinkerFactory;
use Eris\TerminationCondition\TerminationConditionCollection;
use Eris\TerminationCondition\TimeBasedTerminationCondition;
use Eris\Value\Value;
use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\Constraint;
use RuntimeException;

use function class_implements;
use function crc32;
use function getenv;
use function in_array;
use function microtime;
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

    private AntecedentCollection $antecedents;

    private TerminationConditionCollection $terminationConditions;

    private ListenerCollection $listeners;

    private int $seed;

    private bool $shrinkingDisabled = false;

    /**
     * @param list<Generator<mixed>> $generators
     */
    public function __construct(array $generators)
    {
        $this->generators  = $generators;
        $this->growthClass = TriangularGrowth::class;
        $this->sourceClass = RandSource::class;
        $this->listeners   = new ListenerCollection();
        $this->antecedents = new AntecedentCollection();
        $this->terminationConditions = new TerminationConditionCollection();
        $this->shrinkerFactory = new ShrinkerFactory();
        $this->seed        = crc32(microtime());
    }

    public function limitTo(int|DateInterval $limit): self
    {
        if ($limit instanceof DateInterval) {
            $terminationCondition = new TimeBasedTerminationCondition('time', $limit);

            return $this->stopOn($terminationCondition);
        }

        return $this->withMaximumIterations($limit);
    }

    public function listenTo(Listener $listener): self
    {
        if ($listener instanceof MinimumEvaluations) {
            $this->listeners->removeListenerOfType(MinimumEvaluations::class);
        }

        $this->listeners->add($listener);

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
        $this->terminationConditions->add($terminationCondition);

        return $this;
    }

    /**
     * @param string|class-string<Growth> $growth
     */
    public function withGrowth(string $growth): self
    {
        if (class_exists($growth)) {

            $interfaces = class_implements($growth, true);

            /**
             * @psalm-suppress PropertyTypeCoercion
             */
            if ($interfaces && in_array(Growth::class, $interfaces)) {
                $this->growthClass = $growth;

                return $this;
            }
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
        if (class_exists($source)) {
            $interfaces = class_implements($source, true);

            /**
             * @psalm-suppress PropertyTypeCoercion
             */
            if ($interfaces && in_array(Source::class, $interfaces)) {
                $this->sourceClass = $source;

                return $this;
            }
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

    public function withSeed(int $seed): self
    {
        $this->seed = $seed;

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
            $this->antecedents->add(new IndependentConstraintsAntecedent([$firstArgument] + $arguments));
            return $this;
        }

        if ($firstArgument instanceof Antecedent) {
            $this->antecedents->add($firstArgument);
            return $this;
        }

        $this->antecedents->add(new SingleCallbackAntecedent($firstArgument));

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

        $range->seed($this->seed);

        $this->terminationConditions->startPropertyVerification();
        $this->listeners->startPropertyVerification();

        try {
            for (
                $iteration = 0;
                $iteration < $this->maximumIterations && !$this->terminationConditions->shouldTerminate();
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

                $this->listeners->newGeneration($generation->value(), $iteration);

                if (!$this->antecedents->evaluate($values)) {
                    continue;
                }

                $ordinaryEvaluations++;

                try {
                    $assertion(...$generation->value());
                } catch (AssertionFailedError $exception) {
                    $this->listeners->failure($generation->value(), $exception);

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
                            fn (Value $generation): bool => $this->antecedents->evaluate($generation->value())
                        )
                        ->onAttempt(
                            /**
                             * @param Value<array> $generation
                             */
                            function (Value $generation): void {
                                $this->listeners->shrinking($generation->value());
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
            $this->listeners->endPropertyVerification($ordinaryEvaluations, $this->maximumIterations, $redTestException);
        }
    }
}
