<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\choose;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class MinimumEvaluationsTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     *
     * @throws OutOfBoundsException
     */
    public function failsBecauseOfTheLowEvaluationRatio(): void
    {
        $this
            ->forAll(
                choose(0, 100)
            )
            ->when(static fn (int $n): bool => $n > 90)
            ->then(function (int $number): void {
                $this->assertGreaterThan(90, $number);
            });
    }

    /**
     * @test
     */
    public function passesBecauseOfTheArtificiallyLowMinimumEvaluationRatio(): void
    {
        $this
            ->minimumEvaluationRatio(0.01)
            ->forAll(
                choose(0, 100)
            )
            ->when(static fn (int $n): bool => $n > 90)
            ->then(function (int $number): void {
                $this->assertGreaterThan(90, $number);
            });
    }

    /**
     * @test
     *
     * @eris-ratio 1
     */
    public function passesBecauseOfTheArtificiallyLowMinimumEvaluationRatioFromAnnotation(): void
    {
        $this
            ->forAll(
                choose(0, 100)
            )
            ->when(static fn (int $n): bool => $n > 90)
            ->then(function (int $number): void {
                $this->assertGreaterThan(90, $number);
            });
    }
}
