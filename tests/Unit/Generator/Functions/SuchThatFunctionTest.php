<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Contracts\Generator;
use Eris\Generator\SuchThatGenerator;
use Eris\Value\Value;

use function Eris\Generator\suchThat;

/**
 * @covers Eris\Generator\suchThat
 *
 * @uses Eris\Generator\SuchThatGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class suchThatFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsASuchThatGenerator(): void
    {
        $generator = $this->getMockForAbstractClass(Generator::class);
        $generator->method('__invoke')->willReturn(new Value(1), new Value(2), new Value(-5));

        $dut = suchThat(static fn (int $value): bool => $value < 0, $generator);
        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(SuchThatGenerator::class, $dut);
        $this->assertSame(-5, $actual);
    }
}
