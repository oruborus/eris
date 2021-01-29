<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\FloatGenerator;

use function Eris\Generator\float;

/**
 * @covers Eris\Generator\float
 *
 * @uses Eris\Generator\FloatGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FloatFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAFloatGenerator(): void
    {
        $dut = float();

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(FloatGenerator::class, $dut);
        $this->assertThat($actual, $this->logicalOr(
            $this->greaterThanOrEqual($this->size),
            $this->lessThanOrEqual($this->size)
        ));
    }
}
