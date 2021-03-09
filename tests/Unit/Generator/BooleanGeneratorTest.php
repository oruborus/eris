<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\BooleanGenerator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Test\Unit\Generator\GeneratorTestCase;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class BooleanGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\BooleanGenerator::__invoke
     */
    public function generatesBothPossibleBooleanValues(): void
    {
        $rand = $this->createMock(RandomRange::class);
        $rand
            ->expects($this->exactly(2))
            ->method('rand')
            ->with(0, 1)
            ->willReturn(0, 1);

        $dut = new BooleanGenerator();

        $this->assertFalse($dut->__invoke($size = 0, $rand)->value());
        $this->assertTrue($dut->__invoke($size = 0, $rand)->value());
    }

    /**
     * @test
     *
     * @covers Eris\Generator\BooleanGenerator::__invoke
     * @covers Eris\Generator\BooleanGenerator::shrink
     *
     * @dataProvider provideBooleanValues
     *
     * @param Value<bool> $value
     */
    public function allwaysShrinksToFalse(Value $value): void
    {
        $dut = new BooleanGenerator();

        $actual = $dut->shrink($value)->last()->value();

        $this->assertFalse($actual);
    }

    /**
     * @return array<string, array<Value<bool>>>
     */
    public function provideBooleanValues(): array
    {
        return [
            'false value' => [new Value(false)],
            'true value'  => [new Value(true)],
        ];
    }
}
