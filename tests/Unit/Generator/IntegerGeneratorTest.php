<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\IntegerGenerator;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function Eris\Generator\nat;
use function Eris\Generator\neg;
use function Eris\Generator\pos;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class IntegerGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\IntegerGenerator::__construct
     * @covers Eris\Generator\IntegerGenerator::__invoke
     */
    public function generatesRandomIntegersInTheWholeRange(): void
    {
        $dut = new IntegerGenerator();

        for ($i = 0; $i < 100; $i++) {
            $actual = $dut($this->size, $this->rand)->value();

            $this->assertGreaterThanOrEqual(PHP_INT_MIN, $actual);
            $this->assertLessThanOrEqual(PHP_INT_MAX, $actual);
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\IntegerGenerator::shrink
     *
     * @uses Eris\Generator\IntegerGenerator::__construct
     * @uses Eris\Generator\IntegerGenerator::__invoke
     */
    public function shrinksLinearlyTowardsZero(): void
    {
        $dut = new IntegerGenerator();

        $value = $dut($this->size, $this->rand);

        for ($i = 0; $i < 20; $i++) {
            $value = $dut->shrink($value)->last();
        }

        $this->assertSame(0, $value->value());
    }

    /**
     * @test
     *
     * @covers Eris\Generator\IntegerGenerator::shrink
     *
     * @uses Eris\Generator\IntegerGenerator::__construct
     * @uses Eris\Generator\IntegerGenerator::__invoke
     *
     * @dataProvider provideGeneratedValues
     *
     * @param Value<int> $value
     * @param ValueCollection<int> $expected
     */
    public function offersMultiplePossibilitiesForShrinkingTowardsZero(Value $value, ValueCollection $exepcted): void
    {
        $dut = new IntegerGenerator();

        $shrinkingOptions = $dut->shrink($value);

        $this->assertEquals($exepcted, $shrinkingOptions);
    }

    public function provideGeneratedValues(): array
    {
        return [
            'positive' => [
                new Value(100),
                new ValueCollection([
                    new Value(50), new Value(75), new Value(88), new Value(94), new Value(97), new Value(99),
                ])
            ],
            'negative' => [
                new Value(-100),
                new ValueCollection([
                    new Value(-50), new Value(-75), new Value(-88), new Value(-94), new Value(-97), new Value(-99),
                ])
            ],
        ];
    }

    /**
     * @test
     *
     * @covers Eris\Generator\IntegerGenerator::__construct
     * @covers Eris\Generator\IntegerGenerator::__invoke
     */
    public function uniformity(): void
    {
        $dut = new IntegerGenerator();

        $values = [];
        for ($i = 0; $i < 1000; $i++) {
            $values[] = $dut($this->size, $this->rand)->value();
        }
        $this->assertGreaterThan(
            400,
            count(array_filter($values, static fn (int $n): bool => $n > 0)),
            "The positive numbers should be a vast majority given the interval [-10, 10000]"
        );
    }

    /**
     * @test
     *
     * @covers Eris\Generator\IntegerGenerator::shrink
     *
     * @uses Eris\Generator\IntegerGenerator::__construct
     * @uses Eris\Generator\IntegerGenerator::__invoke
     */
    public function shrinkingStopsToZero(): void
    {
        $dut = new IntegerGenerator();

        $generatedValue = $dut($size = 0, $this->rand);
        $shrunkValue = $dut->shrink($generatedValue)->last()->value();

        $this->assertSame(0, $shrunkValue);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\pos
     *
     * @covers Eris\Generator\IntegerGenerator::__construct
     * @covers Eris\Generator\IntegerGenerator::__invoke
     */
    public function posAlreadyStartsFromStrictlyPositiveValues(): void
    {
        $dut = pos();

        $generatedValue = $dut->__invoke(0, $this->rand)->value();

        $this->assertGreaterThan(0, $generatedValue);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\pos
     *
     * @covers Eris\Generator\IntegerGenerator::__construct
     * @covers Eris\Generator\IntegerGenerator::__invoke
     * @covers Eris\Generator\IntegerGenerator::shrink
     */
    public function posNeverShrinksToZero(): void
    {
        $dut = pos();

        $value = $dut->__invoke(10, $this->rand);

        for ($i = 0; $i < 20; $i++) {
            $value = $dut->shrink($value)->last();

            $this->assertNotEquals(0, $value->value());
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\neg
     *
     * @covers Eris\Generator\IntegerGenerator::__construct
     * @covers Eris\Generator\IntegerGenerator::__invoke
     */
    public function negAlreadyStartsFromStrictlyNegativeValues(): void
    {
        $dut = neg();

        $generatedValue = $dut->__invoke(0, $this->rand)->value();

        $this->assertLessThan(0, $generatedValue);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\neg
     *
     * @covers Eris\Generator\IntegerGenerator::__construct
     * @covers Eris\Generator\IntegerGenerator::__invoke
     * @covers Eris\Generator\IntegerGenerator::shrink
     */
    public function negNeverShrinksToZero(): void
    {
        $dut = neg();

        $value = $dut->__invoke(10, $this->rand);

        for ($i = 0; $i < 20; $i++) {
            $value = $dut->shrink($value)->last();

            $this->assertNotEquals(0, $value->value());
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\nat
     *
     * @covers Eris\Generator\IntegerGenerator::__construct
     * @covers Eris\Generator\IntegerGenerator::__invoke
     */
    public function natStartsFromZero(): void
    {
        $dut = nat();

        $generatedValue = $dut->__invoke(0, $this->rand)->value();

        $this->assertSame(0, $generatedValue);
    }
}
