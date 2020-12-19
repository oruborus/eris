<?php

namespace Eris\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Value\Value;
use PHPUnit\Framework\TestCase;

class SuchThatGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->size = 10;
        $this->rand = new RandomRange(new RandSource());
    }

    public function testGeneratesAValueObject()
    {
        $generator = new SuchThatGenerator(
            function ($n) {
                return $n % 2 == 0;
            },
            ConstantGenerator::box(10)
        );
        $this->assertSame(
            10,
            $generator->__invoke($this->size, $this->rand)->unbox()
        );
    }

    public function testAcceptsPHPUnitConstraints()
    {
        $generator = new SuchThatGenerator(
            $this->callback(function ($n) {
                return $n % 2 == 0;
            }),
            ConstantGenerator::box(10)
        );
        $this->assertSame(
            10,
            $generator->__invoke($this->size, $this->rand)->unbox()
        );
    }

    public function testShrinksTheOriginalInput()
    {
        $generator = new SuchThatGenerator(
            function ($n) {
                return $n % 2 == 0;
            },
            new ChooseGenerator(0, 100)
        );
        $element = $generator->__invoke($this->size, $this->rand);
        for ($i = 0; $i < 100; $i++) {
            $element = $generator->shrink($element)->last();

            $this->assertSame(
                0,
                $element->unbox() % 2,
                "Element should still be filtered while shrinking: " . var_export($element, true)
            );
        }
    }

    public function testGivesUpGenerationIfTheFilterIsNotSatisfiedTooManyTimes()
    {
        $this->expectException(SkipValueException::class);
        $generator = new SuchThatGenerator(
            function ($n) {
                return $n % 2 == 0;
            },
            ConstantGenerator::box(11)
        );
        $generator->__invoke($this->size, $this->rand);
    }

    public function testGivesUpShrinkingIfTheFilterIsNotSatisfiedTooManyTimes()
    {
        $generator = new SuchThatGenerator(
            function ($n) {
                return $n % 250 == 0;
            },
            new ChooseGenerator(0, 1000)
        );
        $unshrinkable = new Value(470);
        $this->assertEquals(
            $unshrinkable,
            $generator->shrink($unshrinkable)->last()
        );
    }

    public function testShrinksMultipleOptionsButFiltersTheOnesThatSatisfyTheCondition()
    {
        $generator = new SuchThatGenerator(
            function ($n) {
                return $n % 2 == 0;
            },
            new IntegerGenerator()
        );
        $element = new Value(100);
        $options = $generator->shrink($element);
        foreach ($options as $option) {
            $this->assertTrue(
                $option->unbox() % 2 === 0,
                "Option should still be filtered while shrinking: " . var_export($option, true)
            );
        }
    }

    public function testThanksToMultipleShrinkingItCanBeLikelyToFindShrunkValuesWithRespectToOnlyFollowingThePessimistRoute()
    {
        $generator = new SuchThatGenerator(
            function ($n) {
                return $n < 250;
            },
            new IntegerGenerator()
        );
        $unshrinkable = new Value(470);
        $options = $generator->shrink($unshrinkable);
        $this->assertGreaterThan(0, count($options));
        foreach ($options as $option) {
            $this->assertLessThan(250, $option->unbox());
        }
    }
}
