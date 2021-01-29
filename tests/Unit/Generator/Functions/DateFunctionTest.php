<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use DateTime;
use Eris\Generator\DateGenerator;

use function Eris\Generator\date;

/**
 * @covers Eris\Generator\date
 *
 * @uses Eris\Generator\DateGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class DateFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @dataProvider provideBoundsForDate
     */
    public function createsADateGenerator(
        ?DateTime $lower,
        ?DateTime $upper,
        int $expectedLower,
        int $expectedUpper
    ): void {
        $dut = date($lower, $upper);

        $actual = $dut($this->size, $this->rand)->value()->getTimestamp();

        $this->assertInstanceOf(DateGenerator::class, $dut);
        $this->assertThat($actual, $this->logicalOr(
            $this->greaterThanOrEqual($expectedLower),
            $this->lessThanOrEqual($expectedUpper),
        ));
    }

    /**
     * @return array<string, array>
     */
    public function provideBoundsForDate(): array
    {
        return [
            'neither lower nor upper bound given' => [null, null, 0, 2 ** 31 - 1],
            'only upper bound given'              => [null, new DateTime('@10'), 0, 10],
            'only lower bound given'              => [new DateTime('@2147483637'), null, 2 ** 31 - 11, 2 ** 31 - 1],
            'both bounds given'                   => [new DateTime('@20'), new DateTime('@30'), 20, 30],
        ];
    }
}
