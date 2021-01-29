<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Contracts\Generator;
use Eris\Generator\VectorGenerator;
use Eris\Value\Value;

use function Eris\Generator\vector;

/**
 * @covers Eris\Generator\vector
 *
 * @uses Eris\Generator\AssociativeArrayGenerator
 * @uses Eris\Generator\boxAll
 * @uses Eris\Generator\TupleGenerator
 * @uses Eris\Generator\VectorGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class VectorFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAVectorGeneratorFromArrayOfGenerators(): void
    {
        $generator1 = $this->getMockForAbstractClass(Generator::class);
        $generator1->method('__invoke')->willReturn(new Value(1));

        $dut = vector(5, $generator1);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(VectorGenerator::class, $dut);
        $this->assertSame([1, 1, 1, 1, 1], $actual);
    }
}
