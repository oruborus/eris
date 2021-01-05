<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\ExpectationFailedException;

use function Eris\Generator\nat;

function my_sum(int $first, int $second): int
{
    return ($first >= 42 ? 1 : 0) + $first + $second;
}

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SumTest extends TestCase
{
    use TestTrait;

    /**
     * This test fails as there are natural numbers for which my_sum returns an incorrect result.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function rightIdentityElement(): void
    {
        $this
            ->forAll(
                nat(1000)
            )
            ->then(function (int $number): void {
                $this->assertSame($number, my_sum($number, 0), "Summing {$number} to 0");
            });
    }

    /**
     * @test
     */
    public function leftIdentityElement(): void
    {
        $this
            ->forAll(
                nat(1000)
            )
            ->then(function (int $number): void {
                $this->assertSame($number, my_sum(0, $number), "Summing 0 to {$number}");
            });
    }


    /**
     * This test fails as there are natural numbers for which my_sum returns an incorrect result.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function equalToReferencePhpImplementation(): void
    {
        $this
            ->forAll(
                nat(1000),
                nat(1000)
            )
            ->then(function (int $first, int $second): void {
                $this->assertSame($first + $second, my_sum($first, $second), "Summing {$first} and {$second}");
            });
    }

    /**
     * This test fails as a sum of natural numbers is never negative.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function propertyNeverSatisfied(): void
    {
        $this
            ->forAll(
                nat(1000),
                nat(1000)
            )
            ->then(function (int $first, int $second): void {
                $this->assertSame(-1, my_sum($first, $second), "Summing {$first} and {$second}");
            });
    }
}
