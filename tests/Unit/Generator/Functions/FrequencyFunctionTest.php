<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\FrequencyGenerator;

use function Eris\Generator\frequency;

/**
 * @covers Eris\Generator\frequency
 *
 * @uses Eris\Generator\box
 * @uses Eris\Generator\ConstantGenerator
 * @uses Eris\Generator\FrequencyGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FrequencyFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAFrequencyGenerator(): void
    {
        $dut = frequency([1, 5]);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(FrequencyGenerator::class, $dut);
        $this->assertSame(5, $actual);
    }
}
