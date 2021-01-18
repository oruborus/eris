<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ChooseGenerator;
use Eris\Generator\ConstantGenerator;
use Eris\Generator\MapGenerator;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class MapGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\MapGenerator::__construct
     * @covers Eris\Generator\MapGenerator::__invoke
     *
     * @uses Eris\Generator\ConstantGenerator
     */
    public function generatesAValueObject(): void
    {
        $dut = new MapGenerator(
            static fn (int $n): int => $n * 2,
            ConstantGenerator::box(1)
        );

        $actual = $dut->__invoke($this->size, $this->rand)->value();

        $this->assertSame(2, $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\MapGenerator::__construct
     * @covers Eris\Generator\MapGenerator::shrink
     *
     * @uses Eris\Generator\MapGenerator::__invoke
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function shrinksTheOriginalInput(): void
    {
        $dut = new MapGenerator(
            static fn (int $n): int => $n * 2,
            new ChooseGenerator(1, 100)
        );

        $element = $dut->__invoke($this->size, $this->rand);
        $expected = $element->value();

        $actual = $dut->shrink($element)->last()->value();

        $this->assertLessThanOrEqual($expected, $actual, "Element should have diminished in size");
    }
}
