<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use OutOfBoundsException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

use function count;
use function Eris\Generator\choose;
use function Eris\Generator\elements;
use function Eris\Generator\seq;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class WhenTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function whenWithAnAnonymousFunctionWithGherkinSyntax(): void
    {
        $this
            ->forAll(
                choose(0, 1000)
            )
            ->when(static fn (int $n): bool => $n > 42)
            ->then(function (int $number): void {
                $this->assertGreaterThan(42, $number);
            });
    }

    /**
     * @test
     */
    public function whenWithAnAnonymousFunctionForMultipleArguments(): void
    {
        $this
            ->forAll(
                choose(0, 1000),
                choose(0, 1000)
            )
            ->when(static fn (int $first, int $second): bool => $first > 42 && $second > 23)
            ->then(function (int $first, int $second): void {
                $this->assertGreaterThan(
                    42 + 23,
                    $first + $second,
                    "\$first and \$second were filtered to be more than 42 and 23, but they are {$first} and {$second}."
                );
            });
    }

    /**
     * @test
     */
    public function whenWithOnePHPUnitConstraint(): void
    {
        $this
            ->forAll(
                choose(0, 1000)
            )
            ->when($this->greaterThan(42))
            ->then(function (int $number): void {
                $this->assertGreaterThan(42, $number);
            });
    }

    /**
     * @test
     */
    public function whenWithMultiplePHPUnitConstraints(): void
    {
        $this
            ->forAll(
                choose(0, 1000),
                choose(0, 1000)
            )
            ->when(
                $this->greaterThan(42),
                $this->greaterThan(23)
            )
            ->then(function (int $first, int $second): void {
                $this->assertGreaterThan(
                    42 + 23,
                    $first + $second,
                    "\$first and \$second were filtered to be more than 42 and 23, but they are {$first} and {$second}."
                );
            });
    }

    public function multipleWhenClausesWithGherkinSyntax(): void
    {
        $this
            ->forAll(
                choose(0, 1000)
            )
            ->when($this->greaterThan(42))
            ->and($this->lessThan(900))
            ->then(function (int $number): void {
                $this->assertGreaterThan(42, $number);
                $this->assertLessThan(900, $number);
            });
    }

    /**
     * This test fails as there are only 20% of possible values greater than 800, which is well under the default
     * evaluation ratio of 50%.
     *
     * @test
     *
     * @throws OutOfBoundsException
     */
    public function whenWhichSkipsTooManyValues(): void
    {
        $this
            ->forAll(
                choose(0, 1000)
            )
            ->when($this->greaterThan(800))
            ->then(function (int $number): void {
                $this->assertGreaterThan(800, $number);
            });
    }

    /**
     * This test will fail as all values don't fulfill the assertion. 
     * The current implementation shows no problem as PHPUnit prefers to show the exception (ExpectationFailedException)
     * from the test method rather than the one from teardown (OutOfBoundsException due to low evaluation ratio)
     * when both fail.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function whenFailingWillNaturallyHaveALowEvaluationRatioSoWeDontWantThatErrorToObscureTheTrueOne(): void
    {
        $this
            ->forAll(
                choose(0, 1000)
            )
            ->when($this->greaterThan(100))
            ->then(function (int $number): void {
                $this->assertLessThanOrEqual(100, $number);
            });
    }

    /**
     * @test
     */
    public function sizeIncreasesEvenIfEvaluationsAreSkippedDueToAntecedentsNotBeingSatisfied(): void
    {
        $this
            ->forAll(
                seq(
                    elements(1, 2, 3)
                )
            )
            ->when(static fn (array $seq): bool => count($seq) > 0)
            ->then(function (array $seq): void {
                $this->assertGreaterThan(0, count($seq));
            });
    }
}
