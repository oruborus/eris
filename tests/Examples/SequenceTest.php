<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function array_reverse;
use function count;
use function Eris\Generator\nat;
use function Eris\Generator\seq;
use function sort;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SequenceTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function arrayReversePreserveLength(): void
    {
        $this
            ->forAll(
                seq(nat())
            )
            ->then(function (array $array): void {
                $this->assertSame(count($array), count(array_reverse($array)));
            });
    }

    /**
     * @test
     */
    public function arrayReverse(): void
    {
        $this
            ->forAll(
                seq(nat())
            )
            ->then(function (array $array): void {
                $this->assertSame($array, array_reverse(array_reverse($array)));
            });
    }

    /**
     * @test
     */
    public function arraySortingIsIdempotent(): void
    {
        $this
            ->forAll(
                seq(nat())
            )
            ->then(function (array $array): void {
                sort($array);
                $expected = $array;
                sort($array);
                $this->assertSame($expected, $array);
            });
    }
}
