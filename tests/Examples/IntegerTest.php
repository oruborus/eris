<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\byte;
use function Eris\Generator\int;
use function Eris\Generator\neg;
use function Eris\Generator\pos;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class IntegerTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function sumIsCommutative(): void
    {
        $this
            ->forAll(
                int(),
                int()
            )
            ->then(function (int $first, int $second): void {
                $x = $first + $second;
                $y = $second + $first;

                $this->assertSame($x, $y, "Sum between {$first} and {$second} should be commutative");
            });
    }

    /**
     * @test
     */
    public function sumIsAssociative(): void
    {
        $this
            ->forAll(
                int(),
                neg(),
                pos()
            )
            ->then(function (int $first, int $second, int $third): void {
                $x = $first + ($second + $third);
                $y = ($first + $second) + $third;

                $this->assertSame($x, $y, "Sum between {$first} and {$second} should be associative");
            });
    }

    /**
     * @test
     */
    public function byteData(): void
    {
        $this
            ->forAll(
                byte()
            )
            ->then(function (int $byte): void {
                $this->assertGreaterThanOrEqual(0, $byte, "{$byte} is not a valid value for a byte");
                $this->assertLessThanOrEqual(255, $byte, "{$byte} is not a valid value for a byte");
            });
    }
}
