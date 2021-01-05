<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\ExpectationFailedException;

use function abs;
use function Eris\Generator\float;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FloatTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function aPropertyHoldingForAllNumbers(): void
    {
        $this
            ->forAll(
                float()
            )
            ->then(function (float $number): void {
                $this->assertSame(0.0, abs($number) - abs($number));
            });
    }

    /**
     * This test fails as the FloatGenerator genertes negative numbers as well.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function aPropertyHoldingOnlyForPositiveNumbers(): void
    {
        $this
            ->forAll(
                float()
            )
            ->then(function (float $number): void {
                $this->assertGreaterThanOrEqual(0.0, $number, "{$number} is not a (loosely) positive number");
            });
    }
}
