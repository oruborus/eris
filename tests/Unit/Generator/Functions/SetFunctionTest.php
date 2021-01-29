<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Contracts\Generator;
use Eris\Generator\SetGenerator;
use Eris\Value\Value;

use function Eris\Generator\set;

/**
 * @covers Eris\Generator\set
 *
 * @uses Eris\Generator\SetGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SetFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsASetGenerator(): void
    {
        $generator = $this->getMockForAbstractClass(Generator::class);
        $generator->method('__invoke')->willReturn(new Value(5));

        $dut = set($generator);

        $actual = $dut($this->size, $this->rand);
        $actual = $actual->value();

        $this->assertInstanceOf(SetGenerator::class, $dut);
        $this->assertSame([5], $actual);
    }
}
