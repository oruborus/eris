<?php

declare(strict_types=1);

namespace Eris\Quantifier;

use DateInterval;
use InvalidArgumentException;
use Eris\Antecedent\AntecedentCollection;
use Eris\Antecedent\IndependentConstraintsAntecedent;
use Eris\Antecedent\SingleCallbackAntecedent;
use Eris\Contracts\Antecedent;
use Eris\Contracts\Growth;
use Eris\Contracts\Listener;
use Eris\Contracts\Quantifier;
use Eris\Contracts\Source;
use Eris\Contracts\TerminationCondition;
use Eris\Generator\GeneratorCollection;
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
use function class_parents;
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

    private GeneratorCollection $generators;

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

    public function __construct(GeneratorCollection $generators)
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

        $this->listeners[] = $listener;

        return $this;
    }

    /**
     * @see Eris\Quantifier\ForAll::listenTo alias
     * @codeCoverageIgnore
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
        if (class_exists($growth)) {

            $parents = class_parents($growth, true);

            /**
             * @psalm-suppress PropertyTypeCoercion
             */
            if ($parents && in_array(Growth::class, $parents)) {
                $this->growthClass = $growth;

                return $this;
            }
        }

        $growth = strtolower($growth);

        $growthClasses = [
            'linear'     => \Eris\Growth\LinearGrowth::class,
            'triangular' => \Eris\Growth\TriangularGrowth::class,
        ];

        $this->growthClass = $growthClasses[$growth] ??
            throw new InvalidArgumentException("{$growth} in not a valid Growth type");

        return $this;
    }

    /**
     * @see Eris\Quantifier\ForAll::withMaximumSize alias
     * @codeCoverageIgnore
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
     * @see Eris\Quantifier\ForAll::withoutShrinking alias
     * @codeCoverageIgnore
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

        $this->sourceClass = $sourceClasses[$source] ??
            throw new InvalidArgumentException("{$source} in not a valid Source type");

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
     * @see Eris\Quantifier\ForAll::when alias
     * @codeCoverageIgnore
     *
     * @param Antecedent|Constraint|callable(mixed...):bool $firstArgument
     */
    public function and($firstArgument, Constraint ...$arguments): self
    {
        return $this->when($firstArgument, ...$arguments);
    }

    /**
     * @see Eris\Quantifier\ForAll::then alias
     * @codeCoverageIgnore
     *
     * @param callable(mixed...):void $assertion
     */
    public function __invoke($assertion): void
    {
        $this->then($assertion);
    }

    /**
     * @param callable(mixed...):void $assertion
     */
    public function then($assertion): void
    {
        $growth              = new ($this->growthClass)($this->maximumSize, $this->maximumIterations);
        $range               = new RandomRange(new ($this->sourceClass)());
        $ordinaryEvaluations = 0;
        $redTestException    = null;
        $values              = null;
        $shrinker            = $this->shrinkerFactory->multiple($this->generators, $assertion);

        $range->seed($this->seed);

        /**
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
            );

        $this->terminationConditions->startPropertyVerification();
        $this->listeners->startPropertyVerification();

        try {
            for ($iteration = 0; $iteration < $this->maximumIterations; $iteration++) {
                if ($this->terminationConditions->shouldTerminate()) {
                    break;
                }

                try {
                    $values = $this->generators->__invoke($growth[$iteration], $range);
                } catch (SkipValueException $e) {
                    continue;
                }

                $this->listeners->newGeneration($values->value(), $iteration);

                if (!$this->antecedents->evaluate($values->value())) {
                    continue;
                }

                $ordinaryEvaluations++;

                try {
                    $assertion(...$values->value());
                } catch (AssertionFailedError $exception) {
                    $this->listeners->failure($values->value(), $exception);

                    if ($this->shrinkingDisabled) {
                        throw $exception;
                    }

                    $shrinker->from($values, $exception);
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
