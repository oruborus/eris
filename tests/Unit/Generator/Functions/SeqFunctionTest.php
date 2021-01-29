<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Contracts\Generator;
use Eris\Generator\SequenceGenerator;
use Eris\Value\Value;

use function Eris\Generator\seq;

/**
 * @covers Eris\Generator\seq
 *
 * @uses Eris\Generator\AssociativeArrayGenerator
 * @uses Eris\Generator\box
 * @uses Eris\Generator\boxAll
 * @uses Eris\Generator\ConstantGenerator
 * @uses Eris\Generator\SequenceGenerator
 * @uses Eris\Generator\TupleGenerator
 * @uses Eris\Generator\VectorGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SeqFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsASequenceGeneratorFromConstantValue(): void
    {
        $dut = seq(5);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(SequenceGenerator::class, $dut);
        $this->assertIsArray($actual);

        foreach ($actual as $value) {
            $this->assertSame(5, $value);
        }
    }

    /**
     * @test
     */
    public function createsASequenceGeneratorFromGenerator(): void
    {
        $generator = $this->getMockForAbstractClass(Generator::class);
        $generator->method('__invoke')->willReturn(new Value(5));
        $dut = seq($generator);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(SequenceGenerator::class, $dut);
        $this->assertIsArray($actual);

        foreach ($actual as $value) {
            $this->assertSame(5, $value);
        }
    }
}
