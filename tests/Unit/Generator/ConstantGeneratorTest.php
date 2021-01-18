<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ConstantGenerator;
use Test\Unit\Generator\GeneratorTestCase;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ConstantGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\ConstantGenerator::__construct
     * @covers Eris\Generator\ConstantGenerator::__invoke
     *
     * @dataProvider provideConstantValues
     *
     * @param mixed $expected
     */
    public function picksAlwaysTheValue($expected): void
    {
        $dut = new ConstantGenerator($expected);

        for ($i = 0; $i < 50; $i++) {
            $this->assertSame($expected, $dut($this->size, $this->rand)->value());
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\ConstantGenerator::__construct
     * @covers Eris\Generator\ConstantGenerator::shrink
     *
     * @uses Eris\Generator\ConstantGenerator::__invoke
     * @dataProvider provideConstantValues
     *
     * @param mixed $expected
     */
    public function shrinkAlwaysToTheValue($expected): void
    {
        $dut = new ConstantGenerator($expected);

        $element = $dut($this->size, $this->rand);
        for ($i = 0; $i < 50; $i++) {
            $this->assertSame($expected, $dut->shrink($element)->last()->value());
        }
    }

    public function provideConstantValues(): array
    {
        return [
            'integer' => [15],
            'float'   => [M_PI],
            'string'  => ['Hello World!'],
            'array'   => [['A' => M_2_PI, 12335, 'b' => [], null, false]],
            'false'   => [false],
            'true'    => [true],
        ];
    }
}
