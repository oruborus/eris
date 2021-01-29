<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Contracts\Generator;
use Eris\Generator\TupleGenerator;
use Eris\Value\Value;

use function Eris\Generator\tuple;

/**
 * @covers Eris\Generator\tuple
 *
 * @uses Eris\Generator\AssociativeArrayGenerator
 * @uses Eris\Generator\boxAll
 * @uses Eris\Generator\TupleGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TupleFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsATupleGeneratorFromArrayOfGenerators(): void
    {
        $generator1 = $this->getMockForAbstractClass(Generator::class);
        $generator1->method('__invoke')->willReturn(new Value(1));
        $generator2 = $this->getMockForAbstractClass(Generator::class);
        $generator2->method('__invoke')->willReturn(new Value(2));

        $dut = tuple([$generator1, $generator2]);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(TupleGenerator::class, $dut);
        $this->assertSame([1, 2], $actual);
    }

    /**
     * @test
     */
    public function createsATupleGeneratorFromArgumentList(): void
    {
        $generator1 = $this->getMockForAbstractClass(Generator::class);
        $generator1->method('__invoke')->willReturn(new Value(1));
        $generator2 = $this->getMockForAbstractClass(Generator::class);
        $generator2->method('__invoke')->willReturn(new Value(2));

        $dut = tuple($generator1, $generator2);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(TupleGenerator::class, $dut);
        $this->assertSame([1, 2], $actual);
    }
}
