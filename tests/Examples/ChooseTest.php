<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\choose;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ChooseTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function sumOfTwoIntegersFromBoundedRangesIsCommutative(): void
    {
        $this
            ->forAll(
                choose(-1000, 430),
                choose(230, -30000)
            )
            ->then(function (int $first, int $second): void {
                $x = $first + $second;
                $y = $second + $first;

                $this->assertSame($x, $y, "Sum between {$first} and {$second} should be commutative");
            });
    }
}
