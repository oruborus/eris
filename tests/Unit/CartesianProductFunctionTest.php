<?php

declare(strict_types=1);

namespace Test\Unit;

use Generator;
use PHPUnit\Framework\TestCase;

use function Eris\cartesianProduct;
use function iterator_to_array;

/**
 * @covers Eris\cartesianProduct
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CartesianProductFunctionTest extends TestCase
{
    /**
     * @test
     */
    public function returnsGeneratorWhichYieldsEmptyArrayWhenCalledWithEmptyArray(): void
    {
        $argument = [];
        $expected = [[]];

        $dut = cartesianProduct($argument);

        $this->assertInstanceOf(Generator::class, $dut);
        $this->assertSame($expected, iterator_to_array($dut));
    }

    /**
     * @test
     */
    public function returnsGeneratorWhichYieldsAllCombinationsOfInputSetElements(): void
    {
        $argument = [[1, 2, 3], [4, 5, 6], [7, 8, 9]];
        $expected = [
            [1, 4, 7], [1, 4, 8], [1, 4, 9], [1, 5, 7], [1, 5, 8], [1, 5, 9], [1, 6, 7], [1, 6, 8], [1, 6, 9],
            [2, 4, 7], [2, 4, 8], [2, 4, 9], [2, 5, 7], [2, 5, 8], [2, 5, 9], [2, 6, 7], [2, 6, 8], [2, 6, 9],
            [3, 4, 7], [3, 4, 8], [3, 4, 9], [3, 5, 7], [3, 5, 8], [3, 5, 9], [3, 6, 7], [3, 6, 8], [3, 6, 9],
        ];

        $dut = cartesianProduct($argument);

        $this->assertInstanceOf(Generator::class, $dut);
        $this->assertSame($expected, iterator_to_array($dut));
    }

    /**
     * @test
     */
    public function returnsGeneratorWhichYieldsAllCombinationsOfInputSetElementsAndKeepsTheKeys(): void
    {
        $argument = ['A' => [1, 2, 3], 'B' => [4, 5, 6], 'C' => [7, 8, 9]];
        $expected = [
            ['A' => 1, 'B' => 4, 'C' => 7], ['A' => 1, 'B' => 4, 'C' => 8], ['A' => 1, 'B' => 4, 'C' => 9],
            ['A' => 1, 'B' => 5, 'C' => 7], ['A' => 1, 'B' => 5, 'C' => 8], ['A' => 1, 'B' => 5, 'C' => 9],
            ['A' => 1, 'B' => 6, 'C' => 7], ['A' => 1, 'B' => 6, 'C' => 8], ['A' => 1, 'B' => 6, 'C' => 9],
            ['A' => 2, 'B' => 4, 'C' => 7], ['A' => 2, 'B' => 4, 'C' => 8], ['A' => 2, 'B' => 4, 'C' => 9],
            ['A' => 2, 'B' => 5, 'C' => 7], ['A' => 2, 'B' => 5, 'C' => 8], ['A' => 2, 'B' => 5, 'C' => 9],
            ['A' => 2, 'B' => 6, 'C' => 7], ['A' => 2, 'B' => 6, 'C' => 8], ['A' => 2, 'B' => 6, 'C' => 9],
            ['A' => 3, 'B' => 4, 'C' => 7], ['A' => 3, 'B' => 4, 'C' => 8], ['A' => 3, 'B' => 4, 'C' => 9],
            ['A' => 3, 'B' => 5, 'C' => 7], ['A' => 3, 'B' => 5, 'C' => 8], ['A' => 3, 'B' => 5, 'C' => 9],
            ['A' => 3, 'B' => 6, 'C' => 7], ['A' => 3, 'B' => 6, 'C' => 8], ['A' => 3, 'B' => 6, 'C' => 9],
        ];

        $dut = cartesianProduct($argument);

        $this->assertInstanceOf(Generator::class, $dut);
        $this->assertSame($expected, iterator_to_array($dut));
    }
}
