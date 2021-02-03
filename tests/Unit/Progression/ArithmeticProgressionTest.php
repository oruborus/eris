<?php

declare(strict_types=1);

namespace Test\Unit\Progression;

use Eris\Progression\ArithmeticProgression;
use PHPUnit\Framework\TestCase;

use function range;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ArithmeticProgressionTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Progression\ArithmeticProgression
     *
     * @dataProvider provideSeries
     */
    public function returnsValueADefinedStepCloserToLimit(int $start, int $limit, int $step): void
    {
        $values = [...range($start, $limit, $step), $limit, $limit];
        $dut = new ArithmeticProgression($limit, $step);

        $current = $start + ($start <=> $limit) * $step;

        foreach ($values as $expected) {
            $actual = $dut->next($current);
            $current = $actual;

            $this->assertSame($expected, $actual);
        }
    }

    public function provideSeries(): array
    {
        return [
            'values greater than limit and step 1'       => [20, 10, 1],
            'values less than limit and step 1'          => [10, 20, 1],
            'values greater than limit and step 2'       => [20, 10, 2],
            'values less than limit and step 2'          => [10, 20, 2],
            'values greater than limit and step 3'       => [20, 10, 3],
            'values less than limit and step 3'          => [10, 20, 3],
            'values greater than limit and step 3 via 0' => [5, +5, 3],
            'values less than limit and step 3 via 0'    => [-5, 5, 3],
        ];
    }
}
