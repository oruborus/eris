<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ChooseGenerator;
use Eris\Generator\ConstantGenerator;
use Eris\Generator\IntegerGenerator;
use Eris\Generator\SkipValueException;
use Eris\Generator\SuchThatGenerator;
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
class SuchThatGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\SuchThatGenerator::__construct
     * @covers Eris\Generator\SuchThatGenerator::__invoke
     *
     * @uses Eris\Generator\ConstantGenerator
     */
    public function generatesAValueObject(): void
    {
        $dut = new SuchThatGenerator(
            static fn (int $n): bool => $n % 2 === 0,
            ConstantGenerator::box(10)
        );

        $actual = $dut->__invoke($this->size, $this->rand)->value();

        $this->assertSame(10, $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SuchThatGenerator::__construct
     * @covers Eris\Generator\SuchThatGenerator::__invoke
     *
     * @uses Eris\Generator\ConstantGenerator
     */
    public function acceptsPhpunitConstraints(): void
    {
        $dut = new SuchThatGenerator(
            $this->callback(static fn (int $n): bool => $n % 2 === 0),
            ConstantGenerator::box(10)
        );

        $actual = $dut->__invoke($this->size, $this->rand)->value();

        $this->assertSame(10, $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SuchThatGenerator::shrink
     *
     * @uses Eris\Generator\SuchThatGenerator::__construct
     * @uses Eris\Generator\SuchThatGenerator::__invoke
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function shrinksTheOriginalInput(): void
    {
        $dut = new SuchThatGenerator(
            static fn (int $n): bool => $n % 2 === 0,
            new ChooseGenerator(0, 100)
        );

        $element = $dut->__invoke($this->size, $this->rand);
        for ($i = 0; $i < 100; $i++) {
            $element = $dut->shrink($element)->last();

            $this->assertSame(0, $element->value() % 2, "Element should still be filtered while shrinking.");
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SuchThatGenerator::__construct
     * @covers Eris\Generator\SuchThatGenerator::__invoke
     *
     * @uses Eris\Generator\ConstantGenerator
     */
    public function throwsExpceptionIfTheFilterIsNotSatisfiedTooManyTimes(): void
    {
        $this->expectException(SkipValueException::class);

        $dut = new SuchThatGenerator(
            static fn (int $n): bool => $n % 2 === 0,
            ConstantGenerator::box(11)
        );

        $dut->__invoke($this->size, $this->rand);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SuchThatGenerator::shrink
     *
     * @uses Eris\Generator\SuchThatGenerator::__construct
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function givesUpShrinkingIfTheFilterIsNotSatisfiedTooManyTimes(): void
    {
        $dut = new SuchThatGenerator(
            static fn (int $n): bool => $n % 250 === 0,
            new ChooseGenerator(0, 1000)
        );

        $unshrinkable = new Value(470);

        $this->assertEquals($unshrinkable, $dut->shrink($unshrinkable)->last());
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SuchThatGenerator::shrink
     *
     * @uses Eris\Generator\SuchThatGenerator::__construct
     *
     * @uses Eris\Generator\IntegerGenerator
     */
    public function shrinksMultipleOptionsButFiltersTheOnesThatSatisfyTheCondition(): void
    {
        $dut = new SuchThatGenerator(
            static fn (int $n): bool => $n % 2 === 0,
            new IntegerGenerator()
        );

        $element = new Value(100);

        $options = $dut->shrink($element);

        foreach ($options as $option) {
            $this->assertSame(0, $option->value() % 2, "Option should still be filtered while shrinking.");
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SuchThatGenerator::shrink
     *
     * @uses Eris\Generator\SuchThatGenerator::__construct
     *
     * @uses Eris\Generator\IntegerGenerator
     */
    public function thanksToMultipleShrinkingItCanBeLikelyToFindShrunkValuesWithRespectToOnlyFollowingThePessimistRoute(): void
    {
        $dut = new SuchThatGenerator(
            static fn (int $n): bool => $n < 250,
            new IntegerGenerator()
        );

        $unshrinkable = new Value(470);

        $options = $dut->shrink($unshrinkable);

        $this->assertGreaterThan(0, count($options));

        foreach ($options as $option) {
            $this->assertLessThan(250, $option->value());
        }
    }
}
