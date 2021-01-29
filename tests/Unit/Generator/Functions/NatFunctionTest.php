<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\IntegerGenerator;

use function Eris\Generator\nat;

/**
 * @covers Eris\Generator\nat
 *
 * @uses Eris\Generator\IntegerGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class NatFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAnIntegerGeneratorThatOnlyGeneratesNaturalNumbers(): void
    {
        $dut = nat();

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(IntegerGenerator::class, $dut);
        $this->assertGreaterThanOrEqual(0, $actual);
    }
}
