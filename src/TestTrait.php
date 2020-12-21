<?php

namespace Eris;

use BadMethodCallException;
use DateInterval;
use Eris\Contracts\Generator;
use Eris\Contracts\Listener;
use Eris\Listener\MinimumEvaluations;
use Eris\Quantifier\ForAll;
use Eris\Contracts\TerminationCondition;
use Eris\Quantifier\TimeBasedTerminationCondition;
use Eris\Random\MtRandSource;
use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Shrinker\ShrinkerFactory;
use Eris\Value\Value;
use PHPUnit\Util\Test;

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
     * @beforeClass
     *
     * @return void
     * 
     * @psalm-suppress UnresolvableInclude
     */
    public static function erisSetupBeforeClass(): void
    {
        foreach (['Generator', 'Antecedent', 'Listener', 'Random'] as $namespace) {
            foreach (glob(__DIR__ . '/' . $namespace . '/*.php') as $filename) {
                require_once($filename);
            }
        }
    }

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

    protected function limitTo(int|DateInterval $limit): self
    {
        if ($limit instanceof DateInterval) {
            $interval = $limit;
            $terminationCondition = new TimeBasedTerminationCondition('time', $interval);
            $this->listeners[] = $terminationCondition;
            $this->terminationConditions[] = $terminationCondition;
        } else /*if (is_integer($limit))*/ {
            $this->iterations = $limit;
        } /* else {
            throw new InvalidArgumentException("The limit " . var_export($limit, true) . " is not valid. Please pass an integer or DateInterval.");
        }*/
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
     * forAll($generator1, $generator2, ...)
     * @return ForAll
     */
    public function forAll(): ForAll
    {
        $this->randRange->seed($this->seed);
        $generators = func_get_args();
        $quantifier = new ForAll(
            $generators,
            $this->iterations,
            new ShrinkerFactory([
                'timeLimit' => $this->shrinkingTimeLimit,
            ]),
            $this->shrinkerFactoryMethod,
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
