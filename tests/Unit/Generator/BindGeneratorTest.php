<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\BindGenerator;
use Eris\Generator\ChooseGenerator;
use Eris\Generator\ConstantGenerator;
use Eris\Generator\IntegerGenerator;
use Eris\Generator\TupleGenerator;
use Eris\Generator\VectorGenerator;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function count;

/**
 * @uses Eris\cartesianProduct
 * @uses Eris\Generator\AssociativeArrayGenerator
 * @uses Eris\Generator\ChooseGenerator
 * @uses Eris\Generator\ConstantGenerator
 * @uses Eris\Generator\IntegerGenerator
 * @uses Eris\Generator\TupleGenerator
 * @uses Eris\Generator\VectorGenerator
 * @uses Eris\Generator\ensureAreAllGenerators
 * @uses Eris\Generator\ensureIsGenerator
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class BindGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\BindGenerator::__construct
     * @covers Eris\Generator\BindGenerator::__invoke
     */
    public function generatesAValueObject(): void
    {
        $dut = new BindGenerator(
            // TODO: order of parameters should be consistent with map, or not?
            new ConstantGenerator(4),
            static fn (int $n): ChooseGenerator => new ChooseGenerator($n, $n + 10)
        );

        $actual = $dut($this->size, $this->rand);

        $this->assertInstanceOf(Value::class, $actual);
        $this->assertIsInt($actual->value());
    }

    /**
     * @test
     *
     * @covers Eris\Generator\BindGenerator::__invoke
     * @covers Eris\Generator\BindGenerator::shrink
     *
     * @uses Eris\Generator\BindGenerator::__construct
     */
    public function shrinksTheOuterGenerator(): void
    {
        // $this->markTestSkipped('Test fails in current configuration.');
        $dut = new BindGenerator(
            new ChooseGenerator(0, 5),
            static fn (int $n): ChooseGenerator => new ChooseGenerator($n, $n + 10)
        );

        $value = $dut->__invoke($this->size, $this->rand);

        for ($i = 0; $i < 20; $i++) {
            $this->assertIsInt($value->value());
            $value = $dut->shrink($value)->last();
        }

        $this->assertLessThanOrEqual(5, $value->value());
    }

    /**
     * @test
     *
     * @covers Eris\Generator\BindGenerator::__invoke
     *
     * @uses Eris\Generator\BindGenerator::__construct
     */
    public function associativeProperty(): void
    {
        $dut1 = new BindGenerator(
            new BindGenerator(
                new ChooseGenerator(0, 5),
                static fn (int $n): ChooseGenerator => new ChooseGenerator(10 * $n, 10 * $n + 1)
            ),
            static fn (int $m): VectorGenerator => new VectorGenerator($m, new IntegerGenerator())
        );

        $dut2 = new BindGenerator(
            new ChooseGenerator(0, 5),
            static fn (int $n): BindGenerator => new BindGenerator(
                new ChooseGenerator($n * 10, $n * 10 + 1),
                static fn (int $m): VectorGenerator => new VectorGenerator($m, new IntegerGenerator())
            )
        );

        for ($i = 0; $i < 100; $i++) {
            $elementCount1 = count($dut1($this->size, $this->rand)->value());
            $elementCount2 = count($dut2($this->size, $this->rand)->value());

            /**
             * The following assertions check wheter the generated array have an element count with a
             * unit value of either 0 or 1 (e.g. 0, 1, 10, 11, 21, 30, ...)
             */
            $this->assertContains($elementCount1 % 10, [0, 1], "The array has {$elementCount1} elements");
            $this->assertContains($elementCount2 % 10, [0, 1], "The array has {$elementCount2} elements");
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\BindGenerator::__invoke
     * @covers Eris\Generator\BindGenerator::shrink
     *
     * @uses Eris\Generator\BindGenerator::__construct
     */
    public function shrinkBindGeneratorWithCompositeValue(): void
    {
        $dut = new BindGenerator(
            new ChooseGenerator(0, 5),
            static fn (int $n): TupleGenerator => new TupleGenerator([new ConstantGenerator($n)])
        );

        $value             = $dut->__invoke($this->size, $this->rand);
        $firstShrunkValue  = $dut->shrink($value);
        $secondShrunkValue = $dut->shrink($firstShrunkValue->last());

        $this->assertInstanceOf(ValueCollection::class, $secondShrunkValue);
    }
}
