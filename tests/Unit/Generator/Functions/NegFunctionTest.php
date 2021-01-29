<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\IntegerGenerator;

use function Eris\Generator\neg;

/**
 * @covers Eris\Generator\neg
 *
 * @uses Eris\Generator\IntegerGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class NegFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAnIntegerGeneratorThatOnlyGeneratesStrictlyNegativeNumbers(): void
    {
        $dut = neg();

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(IntegerGenerator::class, $dut);
        $this->assertLessThan(0, $actual);
    }
}
