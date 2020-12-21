<?php

declare(strict_types=1);

namespace Test\Unit\Growth;

use Eris\Contracts\Growth;
use Eris\Growth\LinearGrowth;
use PHPUnit\Framework\TestCase;

use function count;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LinearGrowthTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Growth\LinearGrowth::__construct
     * @covers Eris\Growth\LinearGrowth::generateCandidates
     *
     * @uses Eris\Growth\LinearGrowth::limitCandidates
     *
     * @uses Eris\Contracts\Growth
     */
    public function producesAListOfSizesIncreasingThemTriangularly(): void
    {
        $dut = new LinearGrowth(1000);

        $this->assertInstanceOf(Growth::class, $dut);
        $this->assertSame(0, $dut[0]);
        $this->assertSame(1, $dut[1]);
        $this->assertSame(2, $dut[2]);
        $this->assertSame(3, $dut[3]);
        $this->assertSame(4, $dut[4]);
    }

    /**
     * @test
     *
     * @covers Eris\Growth\LinearGrowth::getMaximumSize
     *
     * @uses Eris\Growth\LinearGrowth::__construct
     * @uses Eris\Growth\LinearGrowth::generateCandidates
     * @uses Eris\Growth\LinearGrowth::limitCandidates
     *
     * @uses Eris\Contracts\Growth
     */
    public function maximumSizeCanBeRead(): void
    {
        $dut = new LinearGrowth(500, 100);

        $this->assertSame(500, $dut->getMaximumSize());
    }

    /**
     * @test
     *
     * @covers Eris\Growth\LinearGrowth::getMaximumValue
     *
     * @uses Eris\Growth\LinearGrowth::__construct
     * @uses Eris\Growth\LinearGrowth::generateCandidates
     * @uses Eris\Growth\LinearGrowth::limitCandidates
     *
     * @uses Eris\Contracts\Growth
     */
    public function maximumValueCanBeRead(): void
    {
        $dut = new LinearGrowth(200, 100);

        $this->assertSame(200, $dut->getMaximumValue());
    }

    /**
     * @test
     *
     * @covers Eris\Growth\LinearGrowth::__construct
     * @covers Eris\Growth\LinearGrowth::limitCandidates
     *
     * @uses Eris\Growth\LinearGrowth::generateCandidates
     *
     * @uses Eris\Contracts\Growth
     *
     * @dataProvider provideLimits
     */
    public function coversAnUniformSubsetWhenLimitedToTheNumberOfIterations(int $limit): void
    {
        $dut = new LinearGrowth(1000, $limit);

        $this->assertSame($limit, count($dut));
        $this->assertSame(0, $dut[0]);
        $this->assertSame(0, $dut[$limit]);
        $this->assertSame(1000, $dut[$limit - 1]);
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
