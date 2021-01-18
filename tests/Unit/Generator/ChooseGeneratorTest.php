<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ChooseGenerator;
use Eris\Value\Value;

use function abs;
use function count;
use function array_filter;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ChooseGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\ChooseGenerator::__construct
     * @covers Eris\Generator\ChooseGenerator::__invoke
     */
    public function picksRandomlyAnIntegerAmongBoundaries(): void
    {
        $dut = new ChooseGenerator(-10, 10);

        for ($i = 0; $i < 100; $i++) {
            $value = $dut($this->size, $this->rand)->value();

            $this->assertGreaterThanOrEqual(-10, $value);
            $this->assertLessThanOrEqual(10, $value);
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\ChooseGenerator::shrink
     *
     * @uses Eris\Generator\ChooseGenerator::__construct
     * @uses Eris\Generator\ChooseGenerator::__invoke
     */
    public function shrinksLinearlyTowardsTheSmallerAbsoluteValue(): void
    {
        /* Not a good shrinking policy, it should start to shrink from 0 and move
         * towards the smaller absolute value.
         * To be refactored next.
         */
        $dut = new ChooseGenerator(-10, 200);
        $value = $dut($this->size, $this->rand);
        $target = 10;
        $distance = abs($target - $value->value());

        for ($i = 0; $i < 190; $i++) {
            $newValue = $dut->shrink($value)->last();
            $newDistance = abs($target - $newValue->value());

            $this->assertLessThanOrEqual(
                $distance,
                $newDistance,
                "Failed asserting that {$newDistance} is less than or equal to {$distance}"
            );

            $value = $newValue;
            $distance = $newDistance;
        }

        $this->assertSame($target, $value->value());
    }

    /**
     * @test
     *
     * @covers Eris\Generator\ChooseGenerator::shrink
     *
     * @uses Eris\Generator\ChooseGenerator::__construct
     * @uses Eris\Generator\ChooseGenerator::__invoke
     */
    public function uniformity(): void
    {
        $dut = new ChooseGenerator(-10, 10000);

        $values = [];
        for ($i = 0; $i < 50; $i++) {
            $values[] = $dut($this->size, $this->rand);
        }

        $positiveElementCount = count(array_filter($values, static fn (Value $n): bool => $n->value() > 0));

        $this->assertGreaterThan(
            40,
            $positiveElementCount,
            "The positive numbers should be a vast majority given the interval [-10, 10000]"
        );
    }

    /**
     * @test
     *
     * @covers Eris\Generator\ChooseGenerator::shrink
     *
     * @uses Eris\Generator\ChooseGenerator::__construct
     * @uses Eris\Generator\ChooseGenerator::__invoke
     */
    public function shrinkingStopsToZero(): void
    {
        $dut = new ChooseGenerator($lowerLimit = 0, $upperLimit = 0);
        $value = $dut($this->size, $this->rand);

        $this->assertSame(0, $dut->shrink($value)->last()->value());
    }

    /**
     * @test
     *
     * @covers Eris\Generator\ChooseGenerator::__invoke
     * @covers Eris\Generator\ChooseGenerator::shrink
     *
     * @uses Eris\Generator\ChooseGenerator::__construct
     */
    public function canGenerateSingleInteger(): void
    {
        $dut = new ChooseGenerator(42, 42);

        $generatedValue = $dut($this->size, $this->rand)->value();
        $shrunkValue    = $dut->shrink($dut($this->size, $this->rand))->last()->value();

        $this->assertSame(42, $generatedValue);
        $this->assertSame(42, $shrunkValue);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\ChooseGenerator::__construct
     */
    public function theOrderOfBoundariesDoesNotMatter(): void
    {
        $this->assertEquals(new ChooseGenerator(100, -100), new ChooseGenerator(-100, 100));
    }
}
