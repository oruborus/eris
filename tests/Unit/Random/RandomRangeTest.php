<?php

declare(strict_types=1);

namespace Test\Unit\Random;

use Eris\Contracts\Source;
use Eris\Random\RandomRange;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class RandomRangeTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Random\RandomRange::__construct
     * @covers Eris\Random\RandomRange::seed
     */
    public function canSeedTheSource(): void
    {
        $source = $this->getMockForAbstractClass(Source::class);
        $source->expects($this->once())->method('seed')->will($this->returnSelf());

        $dut = new RandomRange($source);

        $dut->seed(123);
    }

    /**
     * @test
     *
     * @covers Eris\Random\RandomRange::__construct
     * @covers Eris\Random\RandomRange::rand
     */
    public function extractsRandomIntegerFromSourceWithoutBounds(): void
    {
        $source = $this->getMockForAbstractClass(Source::class);
        $source->expects($this->any())->method('extractNumber')->willReturn(123);
        $dut = new RandomRange($source);

        $actual = $dut->rand();

        $this->assertSame(123, $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Random\RandomRange::__construct
     * @covers Eris\Random\RandomRange::rand
     *
     * @dataProvider provideRandomNumberAndBounds
     */
    public function extractsRandomIntegerFromSourceWithinBounds(
        int $extractedNumber,
        int $maximumNumber,
        int $firstBound,
        int $secondBound,
        int $expected
    ): void {
        $source = $this->getMockForAbstractClass(Source::class);
        $source->expects($this->any())->method('extractNumber')->willReturn($extractedNumber);
        $source->expects($this->any())->method('max')->willReturn($maximumNumber);
        $dut = new RandomRange($source);

        $actual = $dut->rand($firstBound, $secondBound);

        $this->assertSame($expected, $actual);
    }

    /**
     * @return array<string, array>
     */
    public function provideRandomNumberAndBounds(): array
    {
        return [
            'extracted number within both bounds'       => [123, 242, 100, 150, 125],
            'extracted number greater than upper bound' => [123, 242, 10, 20, 15],
            'extracted number less than lower bound'    => [123, 242, 125, 135, 130],
            /**
             * Order of bounds does not matter
             */
            'extracted number within both bounds (swapped)'       => [123, 242, 150, 100, 125],
            'extracted number greater than upper bound (swapped)' => [123, 242, 20, 10, 15],
            'extracted number less than lower bound (swapped)'    => [123, 242, 135, 125, 130],
        ];
    }
}
