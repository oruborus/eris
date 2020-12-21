<?php

declare(strict_types=1);

namespace Test\Unit\Growth;

use Eris\Contracts\Growth;
use Eris\Growth\TriangularGrowth;
use PHPUnit\Framework\TestCase;

use function count;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TriangularGrowthTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Growth\TriangularGrowth::__construct
     * @covers Eris\Growth\TriangularGrowth::generateCandidates
     *
     * @uses Eris\Growth\TriangularGrowth::limitCandidates
     *
     * @uses Eris\Contracts\Growth
     */
    public function producesAListOfSizesIncreasingThemTriangularly(): void
    {
        $dut = new TriangularGrowth(1000);

        $this->assertInstanceOf(Growth::class, $dut);
        $this->assertSame(0, $dut[0]);
        $this->assertSame(1, $dut[1]);
        $this->assertSame(3, $dut[2]);
        $this->assertSame(6, $dut[3]);
        $this->assertSame(10, $dut[4]);
    }

    /**
     * @test
     *
     * @covers Eris\Growth\TriangularGrowth::getMaximumSize
     *
     * @uses Eris\Growth\TriangularGrowth::__construct
     * @uses Eris\Growth\TriangularGrowth::generateCandidates
     * @uses Eris\Growth\TriangularGrowth::limitCandidates
     *
     * @uses Eris\Contracts\Growth
     */
    public function maximumSizeCanBeRead(): void
    {
        $dut = new TriangularGrowth(500, 100);

        $this->assertSame(500, $dut->getMaximumSize());
    }

    /**
     * @test
     *
     * @covers Eris\Growth\TriangularGrowth::getMaximumValue
     *
     * @uses Eris\Growth\TriangularGrowth::__construct
     * @uses Eris\Growth\TriangularGrowth::generateCandidates
     * @uses Eris\Growth\TriangularGrowth::limitCandidates
     *
     * @uses Eris\Contracts\Growth
     */
    public function maximumValueCanBeRead(): void
    {
        $dut = new TriangularGrowth(200, 100);

        $this->assertSame(190, $dut->getMaximumValue());
    }

    /**
     * @test
     *
     * @covers Eris\Growth\TriangularGrowth::__construct
     * @covers Eris\Growth\TriangularGrowth::limitCandidates
     *
     * @uses Eris\Growth\TriangularGrowth::generateCandidates
     *
     * @uses Eris\Contracts\Growth
     *
     * @dataProvider provideLimits
     */
    public function coversAnUniformSubsetWhenLimitedToTheNumberOfIterations(int $limit): void
    {
        $dut = new TriangularGrowth(1000, $limit);

        $this->assertSame($limit, count($dut));
        $this->assertSame(0, $dut[0]);
        $this->assertSame(0, $dut[$limit]);
        $this->assertSame(990, $dut[$limit - 1]);
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
