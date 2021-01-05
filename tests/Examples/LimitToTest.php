<?php

declare(strict_types=1);

namespace Test\Examples;

use DateInterval;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\int;
use function usleep;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LimitToTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     *
     * @eris-repeat 5
     */
    public function numberOfIterationsCanBeConfigured(): void
    {
        $this
            ->forAll(
                int()
            )
            ->then(function (int $value): void {
                $this->assertIsInt($value);
            });
    }

    /**
     * @test
     */
    public function timeIntervalToRunForCanBeConfiguredAndAVeryLowNumberOfIterationsCanBeIgnored(): void
    {
        $this
            ->minimumEvaluationRatio(0)
            ->limitTo(new DateInterval('PT2S'))
            ->forAll(
                int()
            )
            ->then(function (int $value): void {
                usleep(100 * 1000);
                $this->assertTrue(true);
            });
    }

    /**
     * @test
     *
     * @eris-ratio 0
     * @eris-duration PT2S
     */
    public function timeIntervalToRunForCanBeConfiguredAndAVeryLowNumberOfIterationsCanBeIgnoredFromAnnotation(): void
    {
        $this
            ->forAll(
                int()
            )
            ->then(function (int $value): void {
                usleep(100 * 1000);
                $this->assertTrue(true);
            });
    }

    // /**
    //  * @test
    //  *
    //  * future feature
    //  */
    // public function timeIntervalToRunForCanBeConfiguredButItNeedsToProduceAtLeastHalfOfTheIterationsByDefault(): void
    // {
    //     $this
    //         ->minimum(10)
    //         ->limitTo(new DateInterval("PT2S"))
    //         ->forAll(
    //             int()
    //         )
    //         ->then(function (int $value): void {
    //             usleep(100 * 1000);
    //             $this->assertTrue(true);
    //         });
    // }
}
