<?php

declare(strict_types=1);

namespace Eris;

use DateInterval;
use Eris\Contracts\Generator;
use Eris\Generator\GeneratorCollection;
use Eris\Listener\MinimumEvaluations;
use Eris\Quantifier\ForAll;
use Eris\Quantifier\CanConfigureQuantifier;
use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Value\Value;
use PHPUnit\Util\Test;

use function abs;
use function array_merge;
use function crc32;
use function end;
use function Eris\Generator\boxAll;
use function getenv;
use function intval;
use function microtime;

trait TestTrait
{
    use CanConfigureQuantifier;

    abstract public function hasFailed(): bool;

    abstract public function toString(): string;

    abstract protected function getName(bool $withDataSet = true): string;

    private array $quantifiers = [];

    private ?int $seed = null;

    /**
     * @codeCoverageIgnore
     */
    public function shrinkingTimeLimit(int $shrinkingTimeLimit): self
    {
        return $this->withShrinkingTimeLimit($shrinkingTimeLimit);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param float $ratio from 0.0 to 1.0
     */
    protected function minimumEvaluationRatio(float $ratio): self
    {
        return $this->listenTo(MinimumEvaluations::ratio($ratio));
    }

    /**
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     */
    private function parseAnnotations(): array
    {
        $defaultAnnotations = [
            'eris-duration' => false,
            'eris-method'   => 'rand',
            'eris-ratio'    => '50',
            'eris-repeat'   => '100',
            'eris-shrink'   => false,
        ];

        ['class' => $classAnnotations, 'method' => $methodAnnotations] =
            Test::parseTestMethodAnnotations(static::class, $this->getName(false));

        $methodAnnotations ??= [];

        foreach ($methodAnnotations as $annotation => $value) {
            $methodAnnotations[$annotation] = end($value);
        }

        foreach ($classAnnotations as $annotation => $value) {
            $classAnnotations[$annotation] = end($value);
        }

        $annotations = array_merge($defaultAnnotations, $classAnnotations, $methodAnnotations);

        return $annotations;
    }

    /**
     * @before
     */
    public function erisSetup(): void
    {
        $this->seed = crc32(microtime());

        if ($seed = intval(getenv('ERIS_SEED'))) {
            $this->seed = abs($seed);
        }

        $this->withSeed($this->seed);

        $annotations = $this->parseAnnotations();

        $this->withRand($annotations['eris-method']);
        $this->withMaximumIterations((int) $annotations['eris-repeat']);
        $this->minimumEvaluationRatio((float) $annotations['eris-ratio'] / 100);

        if ($annotations['eris-shrink']) {
            $this->withShrinkingTimeLimit((int) $annotations['eris-shrink']);
        }

        if ($annotations['eris-duration']) {
            $this->limitTo(new DateInterval($annotations['eris-duration']));
        }
    }

    /**
     * @after
     */
    public function erisTeardown(): void
    {
        if ($this->hasFailed()) {
            $command = PHPUnitCommand::fromSeedAndName($this->seed, $this->toString());
            echo PHP_EOL . "Reproduce with:" . PHP_EOL . $command . PHP_EOL;
        }
    }

    /**
     * @param mixed ...$generators
     */
    public function forAll(...$generators): ForAll
    {
        $generatorCollection = new GeneratorCollection(boxAll($generators));

        $quantifier = new ForAll($generatorCollection);
        $quantifier = $this->getQuantifierBuilder()->build($quantifier);

        $this->quantifiers[] = $quantifier;

        return $quantifier;
    }

    public function sample(Generator $generator, int $times = 10, ?int $size = null): Sample
    {
        return Sample::of($generator, new RandomRange(new RandSource()), $size)->repeat($times);
    }

    public function sampleShrink(Generator $generator, ?Value $fromValue = null, ?int $size = null): Sample
    {
        return Sample::of($generator, new RandomRange(new RandSource()), $size)->shrink($fromValue);
    }
}
