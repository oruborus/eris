<?php

namespace Eris\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use PHPUnit\Framework\TestCase;

class IntegerGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->size = 10;
        $this->rand = new RandomRange(new RandSource());
    }

    public function testPicksRandomlyAnInteger()
    {
        $generator = new IntegerGenerator();
        for ($i = 0; $i < 100; $i++) {
            $this->assertIsInt($generator($this->size, $this->rand)->unbox());
        }
    }

    public function testShrinksLinearlyTowardsZero()
    {
        $generator = new IntegerGenerator();
        $value = $generator($this->size, $this->rand);
        for ($i = 0; $i < 20; $i++) {
            $value = $generator->shrink($value)->last();
        }
        $this->assertSame(0, $value->unbox());
    }

    public function testOffersMultiplePossibilitiesForShrinkingProgressivelySubtracting()
    {
        $generator = new IntegerGenerator();
        $value = new Value(100);
        $shrinkingOptions = $generator->shrink($value);
        $this->assertEquals(
            new ValueCollection([
                new Value(50),
                new Value(75),
                new Value(88),
                new Value(94),
                new Value(97),
                new Value(99),
            ]),
            $shrinkingOptions
        );
    }

    public function testUniformity()
    {
        $generator = new IntegerGenerator();
        $values = [];
        for ($i = 0; $i < 1000; $i++) {
            $values[] = $generator($this->size, $this->rand);
        }
        $this->assertGreaterThan(
            400,
            count(array_filter($values, function ($n) {
                return $n->unbox() > 0;
            })),
            "The positive numbers should be a vast majority given the interval [-10, 10000]"
        );
    }

    public function testShrinkingStopsToZero()
    {
        $generator = new IntegerGenerator();
        $lastValue = $generator($size = 0, $this->rand);
        $this->assertSame(0, $generator->shrink($lastValue)->unbox());
    }

    public function testPosAlreadyStartsFromStrictlyPositiveValues()
    {
        $generator = pos();
        $this->assertGreaterThan(0, $generator->__invoke(0, $this->rand)->unbox());
    }

    public function testPosNeverShrinksToZero()
    {
        $generator = pos();
        $value = $generator->__invoke(10, $this->rand);
        for ($i = 0; $i < 20; $i++) {
            $value = $generator->shrink($value)->last();
            $this->assertNotEquals(0, $value->unbox());
        }
    }

    public function testNegAlreadyStartsFromStrictlyNegativeValues()
    {
        $generator = neg();
        $this->assertLessThan(0, $generator->__invoke(0, $this->rand)->unbox());
    }

    public function testNegNeverShrinksToZero()
    {
        $generator = neg();
        $value = $generator->__invoke(10, $this->rand);
        for ($i = 0; $i < 20; $i++) {
            $value = $generator->shrink($value)->last();
            $this->assertNotEquals(0, $value->unbox());
        }
    }

    public function testNatStartsFromZero()
    {
        $generator = nat();
        $this->assertEquals(0, $generator->__invoke(0, $this->rand)->unbox());
    }
}
