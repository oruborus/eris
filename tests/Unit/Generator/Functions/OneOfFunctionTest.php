<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Contracts\Generator;
use Eris\Generator\OneOfGenerator;
use Eris\Value\Value;

use function Eris\Generator\oneOf;

/**
 * @covers Eris\Generator\oneOf
 *
 * @uses Eris\Generator\box
 * @uses Eris\Generator\FrequencyGenerator
 * @uses Eris\Generator\OneOfGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class OneOfFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAnOneOfGenerator(): void
    {
        $generator = $this->getMockForAbstractClass(Generator::class);
        $generator->method('__invoke')->willReturn(new Value('%VALUE%'));

        $dut = oneOf($generator);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(OneOfGenerator::class, $dut);
        $this->assertSame('%VALUE%', $actual);
    }
}
