<?php

namespace Eris\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Value\Value;
use PHPUnit\Framework\TestCase;

class FloatGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->size = 300;
        $this->rand = new RandomRange(new RandSource());
    }

    public function testPicksUniformelyPositiveAndNegativeFloatNumbers()
    {
        $generator = new FloatGenerator();
        $sum = 0;
        $trials = 500;
        for ($i = 0; $i < $trials; $i++) {
            $value = $generator($this->size, $this->rand);
            $this->assertIsFloat($value->unbox());
            $sum += $value->unbox();
        }
        $mean = $sum / $trials;
        // over a 300 size
        $this->assertLessThan(10, abs($mean));
    }

    public function testShrinksLinearly()
    {
        $generator = new FloatGenerator();
        $this->assertSame(3.5, $generator->shrink(new Value(4.5))->unbox());
        $this->assertSame(-2.5, $generator->shrink(new Value(-3.5))->unbox());
    }

    public function testWhenBothSignsArePossibleCannotShrinkBelowZero()
    {
        $generator = new FloatGenerator();
        $this->assertSame(0.0, $generator->shrink(new Value(0.0))->unbox());
        $this->assertSame(0.0, $generator->shrink(new Value(0.5))->unbox());
        $this->assertSame(0.0, $generator->shrink(new Value(-0.5))->unbox());
    }
}
