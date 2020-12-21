<?php

declare(strict_types=1);

namespace Test\Unit\Quantifier;

use Eris\Quantifier\Size;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SizeTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Quantifier\Size::__construct
     * @covers Eris\Quantifier\Size::getMaxSize
     *
     * @uses Eris\Quantifier\Size::generateList
     * @uses Eris\Quantifier\Size::linearGrowth
     * @uses Eris\Quantifier\Size::triangleNumber
     * @uses Eris\Quantifier\Size::withLinearGrowth
     * @uses Eris\Quantifier\Size::withTriangleGrowth
     */
    public function maximumGenerationSizeIsStored(): void
    {
        $dut1 = Size::withTriangleGrowth(500);
        $dut2 = Size::withLinearGrowth(300);

        $this->assertSame(500, $dut1->getMaxSize());
        $this->assertSame(300, $dut2->getMaxSize());
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\Size::triangleNumber
     * @covers Eris\Quantifier\Size::withTriangleGrowth
     *
     * @uses Eris\Quantifier\Size::__construct
     * @uses Eris\Quantifier\Size::at
     * @uses Eris\Quantifier\Size::generateList
     */
    public function producesAListOfSizesIncreasingThemTriangularly(): void
    {
        $dut = Size::withTriangleGrowth(1000);

        $this->assertEquals(0, $dut->at(0));
        $this->assertEquals(1, $dut->at(1));
        $this->assertEquals(3, $dut->at(2));
        $this->assertEquals(6, $dut->at(3));
        $this->assertEquals(10, $dut->at(4));
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\Size::triangleNumber
     * @covers Eris\Quantifier\Size::withTriangleGrowth
     *
     * @uses Eris\Quantifier\Size::__construct
     * @uses Eris\Quantifier\Size::at
     * @uses Eris\Quantifier\Size::generateList
     */
    public function cyclesThroughAvailableSizesWhenTheyAreFinished(): void
    {
        $dut = Size::withTriangleGrowth(1000);

        $this->assertIsInt($dut->at(42000));
    }


    /**
     * @test
     *
     * @covers Eris\Quantifier\Size::linearGrowth
     * @covers Eris\Quantifier\Size::withLinearGrowth
     *
     * @uses Eris\Quantifier\Size::__construct
     * @uses Eris\Quantifier\Size::at
     * @uses Eris\Quantifier\Size::generateList
     */
    public function allowsLinearGrowth(): void
    {
        $dut = Size::withLinearGrowth(1000);

        $this->assertEquals(0, $dut->at(0));
        $this->assertEquals(1, $dut->at(1));
        $this->assertEquals(2, $dut->at(2));
        $this->assertEquals(3, $dut->at(3));
        $this->assertEquals(4, $dut->at(4));
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\Size::limit
     *
     * @uses Eris\Quantifier\Size::__construct
     * @uses Eris\Quantifier\Size::at
     * @uses Eris\Quantifier\Size::count
     * @uses Eris\Quantifier\Size::generateList
     * @uses Eris\Quantifier\Size::triangleNumber
     * @uses Eris\Quantifier\Size::withTriangleGrowth
     *
     * @dataProvider provideLimits
     */
    public function coversAnUniformSubsetWhenLimitedToTheNumberOfIterations(int $limit): void
    {
        $dut = Size::withTriangleGrowth(1000)->limit($limit);

        $this->assertEquals($limit, count($dut));
        $this->assertEquals(0, $dut->at(0));
        $this->assertEquals(990, $dut->at($limit - 1));
        $this->assertEquals(0, $dut->at($limit));
    }

    public function provideLimits(): array
    {
        return [
            '2 elements'     => [2],
            '5 elements'     => [5],
            '10 elements'    => [10],
            '20 elements'    => [20],
            '100 elements'   => [100],
            '10000 elements' => [10000],
        ];
    }
}
