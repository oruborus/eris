<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\IntegerGenerator;

use function Eris\Generator\pos;

/**
 * @covers Eris\Generator\pos
 *
 * @uses Eris\Generator\IntegerGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class PosFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAnIntegerGeneratorThatOnlyGeneratesStrictlyPositiveNumbers(): void
    {
        $dut = pos();

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(IntegerGenerator::class, $dut);
        $this->assertGreaterThan(0, $actual);
    }
}
