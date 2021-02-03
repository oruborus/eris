<?php

namespace Eris;

use BadMethodCallException;
use DateInterval;
use Eris\Contracts\Generator;
use Eris\Contracts\Listener;
use Eris\Listener\MinimumEvaluations;
use Eris\Quantifier\ForAll;
use Eris\Contracts\TerminationCondition;
use Eris\Growth\TriangularGrowth;
use Eris\Listener\TimeBasedTerminationCondition;
use Eris\Random\MtRandSource;
use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Shrinker\ShrinkerFactory;
use Eris\Value\Value;
use PHPUnit\Util\Test;

use function Eris\Generator\boxAll;

trait TestTrait
{
    abstract public function hasFailed(): bool;
    abstract public function toString(): string;

    // TODO: make this private as much as possible
    // TODO: it's time, extract an object?
    private array $quantifiers = [];
    private int $iterations = 100;
    /**
     * @var Listener[] $listeners
     */
    private array $listeners = [];

    /**
     * @var TerminationCondition[] $terminationConditions
     */
    private array $terminationConditions = [];
    private RandomRange $randRange;
    private string $shrinkerFactoryMethod = 'multiple';
    protected int $seed;
    protected ?int $shrinkingTimeLimit;

    /**
     * @before
     *
     * @return void
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     */
    public function erisSetup(): void
    {
        $this->seedingRandomNumberGeneration();
        $this->listeners = array_filter(
            $this->listeners,
            function (Listener $listener): bool {
                return !($listener instanceof MinimumEvaluations);
            }
        );

        /**
         * @var array<string, array<string, array>> $tags
         */
        $tags = Test::parseTestMethodAnnotations(static::class, $this->getName(false));

        $this->withRand((string) $this->getAnnotationValue($tags, 'eris-method', 'rand', 'strval'));
        $this->iterations = (int) $this->getAnnotationValue($tags, 'eris-repeat', 100, 'intval');
        /** @var ?int */
        $this->shrinkingTimeLimit = $this->getAnnotationValue($tags, 'eris-shrink', null, 'intval');
        $this->listeners[] = MinimumEvaluations::ratio((float) $this->getAnnotationValue($tags, 'eris-ratio', 50, 'floatval') / 100);
        $duration = (string) $this->getAnnotationValue($tags, 'eris-duration', false, 'strval');
        if ($duration) {
            $this->limitTo(new DateInterval($duration));
        }
    }

    /**
     * @internal
     * @return void
     */
    private function seedingRandomNumberGeneration()
    {
        $seed = intval(getenv('ERIS_SEED') ?: (microtime(true) * 1000000));
        if ($seed < 0) {
            $seed *= -1;
        }
        $this->seed = $seed;
    }

    /**
     * @param array<string, array<string, array>> $annotations
     * @param mixed $default
     * @param callable $cast
     * @return mixed
     */
    private function getAnnotationValue(array $annotations, string $key, $default, $cast)
    {
        $annotation = $this->getAnnotation($annotations, $key);
        return isset($annotation[0]) ? $cast($annotation[0]) : $default;
    }

    /**
     * @param array<string, array<string, array>> $annotations
     */
    private function getAnnotation(array $annotations, string $key): array
    {
        if (isset($annotations['method'][$key])) {
            return $annotations['method'][$key];
        }
        return isset($annotations['class'][$key]) ? $annotations['class'][$key] : [];
    }

    /**
     * @after
     *
     * @return void
     */
    public function erisTeardown(): void
    {
        $this->dumpSeedForReproducing();
    }

    /**
     * Maybe: we could add --filter options to the command here,
     * since now the original command is printed.
     *
     * @return void
     */
    private function dumpSeedForReproducing()
    {
        if (!$this->hasFailed()) {
            return;
        }
        $command = PHPUnitCommand::fromSeedAndName($this->seed, $this->toString());
        echo PHP_EOL . "Reproduce with:" . PHP_EOL . $command . PHP_EOL;
    }

    /**
     * @param float $ratio from 0.0 to 1.0
     */
    protected function minimumEvaluationRatio(float $ratio): self
    {
        $this->filterOutListenersOfClass(MinimumEvaluations::class);
        $this->listeners[] = MinimumEvaluations::ratio($ratio);
        return $this;
    }

    /**
     * @param class-string $className
     */
    private function filterOutListenersOfClass(string $className): void
    {
        $this->listeners = array_filter(
            $this->listeners,
            function (Listener $listener) use ($className): bool {
                return !($listener instanceof $className);
            }
        );
    }

    /**
     * @param int|DateInterval $limit
     */
    protected function limitTo($limit): self
    {
        if (is_int($limit)) {
            $this->iterations = $limit;
            return $this;
        }

        $terminationCondition = new TimeBasedTerminationCondition('time', $limit);
        $this->listeners[] = $terminationCondition;
        $this->terminationConditions[] = $terminationCondition;

        return $this;
    }

    /**
     * The maximum time to spend trying to shrink the input after a failed test.
     * The default is no limit.
     */
    protected function shrinkingTimeLimit(int $shrinkingTimeLimit): self
    {
        $this->shrinkingTimeLimit = $shrinkingTimeLimit;
        return $this;
    }

    /**
     * @param string|RandomRange $randFunction mt_rand, rand or a RandomRange
     * @return self
     */
    protected function withRand($randFunction)
    {
        if ($randFunction === 'mt_rand') {
            $this->randRange = new RandomRange(new MtRandSource());
            return $this;
        }
        if ($randFunction === 'rand') {
            $this->randRange = new RandomRange(new RandSource());
            return $this;
        }
        if ($randFunction instanceof RandomRange) {
            $this->randRange = $randFunction;
            return $this;
        }
        throw new BadMethodCallException("When specifying random generators different from the standard ones, you must pass an instance of Eris\\Random\\RandomRange.");
    }

    /**
     * @param list<mixed> $generators
     */
    public function forAll(...$generators): ForAll
    {
        $generators = boxAll($generators);

        $this->randRange->seed($this->seed);

        $quantifier = new ForAll(
            $generators,
            new TriangularGrowth(ForAll::DEFAULT_MAX_SIZE, $this->iterations),
            [new ShrinkerFactory($this->shrinkingTimeLimit), $this->shrinkerFactoryMethod],
            $this->randRange
        );

        foreach ($this->listeners as $listener) {
            $quantifier->hook($listener);
        }

        foreach ($this->terminationConditions as $terminationCondition) {
            $quantifier->stopOn($terminationCondition);
        }

        $this->quantifiers[] = $quantifier;

        return $quantifier;
    }

    public function sample(Generator $generator, int $times = 10, ?int $size = null): Sample
    {
        return Sample::of($generator, $this->randRange, $size)->repeat($times);
    }

    public function sampleShrink(Generator $generator, ?Value $fromValue = null, ?int $size = null): Sample
    {
        return Sample::of($generator, $this->randRange, $size)->shrink($fromValue);
    }
}
