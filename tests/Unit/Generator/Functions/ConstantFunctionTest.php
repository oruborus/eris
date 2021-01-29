<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ConstantGenerator;

use function Eris\Generator\constant;

/**
 * @covers Eris\Generator\constant
 *
 * @uses Eris\Generator\ConstantGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ConstantFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAConstantGenerator(): void
    {
        $dut = constant('%CONSTANT%');

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(ConstantGenerator::class, $dut);
        $this->assertSame('%CONSTANT%', $actual);
    }
}
