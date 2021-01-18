<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\FloatGenerator;
use Eris\Value\Value;

use function abs;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FloatGeneratorTest extends GeneratorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->size = 300;
    }

    /**
     * @test
     *
     * @covers Eris\Generator\FloatGenerator::__invoke
     */
    public function picksUniformelyPositiveAndNegativeFloatNumbers(): void
    {
        $dut = new FloatGenerator();
        $sum = 0;
        $trials = 500;

        for ($i = 0; $i < $trials; $i++) {
            $sum += $dut($this->size, $this->rand)->value();
        }

        $this->assertLessThan(10, abs($sum / $trials));
    }

    /**
     * @test
     *
     * @covers Eris\Generator\FloatGenerator::shrink
     */
    public function shrinksLinearly(): void
    {
        $dut = new FloatGenerator();

        $this->assertSame(3.5, $dut->shrink(new Value(4.5))->last()->value());
        $this->assertSame(-2.5, $dut->shrink(new Value(-3.5))->last()->value());
    }

    /**
     * @test
     *
     * @covers Eris\Generator\FloatGenerator::shrink
     */
    public function whenBothSignsArePossibleCannotShrinkBelowZero(): void
    {
        $dut = new FloatGenerator();

        $this->assertSame(0.0, $dut->shrink(new Value(0.0))->last()->value());
        $this->assertSame(0.0, $dut->shrink(new Value(0.5))->last()->value());
        $this->assertSame(0.0, $dut->shrink(new Value(-0.5))->last()->value());
    }
}
