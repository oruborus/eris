<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ChooseGenerator;
use Eris\Generator\IntegerGenerator;
use Eris\Generator\StringGenerator;
use Eris\Generator\TupleGenerator;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function count;
use function strlen;

/**
 * @uses Eris\Generator\AssociativeArrayGenerator
 * @uses Eris\Generator\ensureIsGenerator
 * @uses Eris\Generator\ensureAreAllGenerators
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TupleGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\TupleGenerator::__construct
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function constructWithAnArrayOfGenerators(): void
    {
        $dut = new TupleGenerator([
            new ChooseGenerator(0, 100),
            new ChooseGenerator(0, 100),
        ]);

        $generated = $dut($this->size, $this->rand);

        $this->assertCount(2, $generated->value());

        foreach ($generated->value() as $element) {
            $this->assertIsInt($element);
            $this->assertGreaterThanOrEqual(0, $element);
            $this->assertLessThanOrEqual(100, $element);
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\TupleGenerator::__construct
     *
     * @uses Eris\Generator\ConstantGenerator
     */
    public function constructWithNonGeneratorValues(): void
    {
        $aNonGenerator = 42;
        $dut = new TupleGenerator([$aNonGenerator]);

        $generated = $dut($this->size, $this->rand);

        foreach ($generated->value() as $element) {
            $this->assertEquals(42, $element);
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\TupleGenerator::__construct
     */
    public function constructWithoutArguments(): void
    {
        $dut = new TupleGenerator([]);

        $this->assertSame([], $dut($this->size, $this->rand)->value());
    }

    /**
     * @test
     *
     * @uses Eris\Generator\TupleGenerator::__construct
     *
     * @uses Eris\cartesianProduct
     * @uses Eris\Generator\ChooseGenerator
     */
    public function shrink(): void
    {
        $dut = new TupleGenerator([
            new ChooseGenerator(0, 100),
            new ChooseGenerator(0, 100),
        ]);

        $elements = $dut->__invoke($this->size, $this->rand);

        /**
         * @var ValueCollection<array{0: int, 1: int}> $elementsAfterShrink
         */
        $elementsAfterShrink = $dut->shrink($elements);

        $this->assertIsInt($elementsAfterShrink->last()->value()[0]);
        $this->assertGreaterThanOrEqual(0, $elementsAfterShrink->last()->value()[0]);
        $this->assertLessThanOrEqual(100, $elementsAfterShrink->last()->value()[0]);
        $this->assertIsInt($elementsAfterShrink->last()->value()[1]);
        $this->assertGreaterThanOrEqual(0, $elementsAfterShrink->last()->value()[1]);
        $this->assertLessThanOrEqual(100, $elementsAfterShrink->last()->value()[1]);

        $this->assertLessThanOrEqual(
            $elements->value()[0] + $elements->value()[1],
            $elementsAfterShrink->last()->value()[0] + $elementsAfterShrink->last()->value()[1],
            var_export(
                [
                    'elements' => $elements,
                    'elementsAfterShrink' => $elementsAfterShrink,
                ],
                true
            )
        );
    }

    /**
     * @test
     *
     * @uses Eris\Generator\TupleGenerator::__construct
     *
     * @uses Eris\cartesianProduct
     * @uses Eris\Generator\ConstantGenerator
     */
    public function doesNotShrinkSomethingAlreadyShrunkToTheMax(): void
    {
        $constants = [42, 42];
        $dut = new TupleGenerator($constants);
        $elements = $dut($this->size, $this->rand);
        $elementsAfterShrink = $dut->shrink($elements);

        $this->assertSame($constants, $elements->value());
        $this->assertSame($constants, $elementsAfterShrink->last()->value());
    }

    /**
     * @test
     *
     * @uses Eris\Generator\TupleGenerator::__construct
     *
     * @uses Eris\cartesianProduct
     * @uses Eris\Generator\IntegerGenerator
     */
    public function shrinkingMultipleOptionsOfOneGenerator(): void
    {
        $dut = new TupleGenerator([
            new IntegerGenerator()
        ]);
        $value = new Value([100], [new Value(100)]);
        $shrunk = $dut->shrink($value);
        $this->assertGreaterThan(1, $shrunk->count());
        foreach ($shrunk as $option) {
            $optionValue = $option->value();
            $this->assertIsArray($optionValue);
            $this->assertEquals(1, count($optionValue));
        }
    }
    /**
     * @test
     *
     * @uses Eris\Generator\TupleGenerator::__construct
     *
     * @uses Eris\cartesianProduct
     * @uses Eris\Generator\StringGenerator
     * @uses Eris\Value\Value
     *
     * @depends shrinkingMultipleOptionsOfOneGenerator
     */
    public function shrinkingMultipleOptionsOfMoreThanOneSingleShrinkingGenerator(): void
    {
        $dut = new TupleGenerator([
            new StringGenerator(),
            new StringGenerator(),
        ]);
        $value = new Value(
            ['hello', 'world'],
            [
                new Value('hello'),
                new Value('world'),
            ]
        );
        $shrunk = $dut->shrink($value);
        // shrinking (a), (b) or (a and b)
        $this->assertEquals(3, $shrunk->count());
        foreach ($shrunk as $option) {
            // $this->assertEquals('tuple', $option->generatorName());
            $optionValue = $option->value();
            $this->assertIsArray($optionValue);
            $this->assertEquals(2, count($optionValue));
            $elementsBeingShrunk =
                (strlen($optionValue[0]) < 5 ? 1 : 0)
                + (strlen($optionValue[1]) < 5 ? 1 : 0);
            $this->assertGreaterThanOrEqual(1, $elementsBeingShrunk);
        }
    }
    /**
     * @test
     *
     * @uses Eris\Generator\TupleGenerator::__construct
     *
     * @uses Eris\cartesianProduct
     * @uses Eris\Generator\IntegerGenerator
     * @uses Eris\Value\Value
     *
     * @depends shrinkingMultipleOptionsOfOneGenerator
     */
    public function shrinkingMultipleOptionsOfMoreThanOneMultipleShrinkingGenerator(): void
    {
        $dut = new TupleGenerator([
            new IntegerGenerator(),
            new IntegerGenerator(),
        ]);
        $value = new Value(
            [100, 200],
            [
                new Value(100),
                new Value(200),
            ]
        );
        $shrunk = $dut->shrink($value);
        $this->assertGreaterThan(1, $shrunk->count());
        foreach ($shrunk as $option) {
            // $this->assertEquals('tuple', $option->generatorName());
            $optionValue = $option->value();
            $this->assertIsArray($optionValue);
            $this->assertEquals(2, count($optionValue));
            $this->assertNotEquals([100, 200], $optionValue);
            $elementsBeingShrunk =
                ($optionValue[0] < 100 ? 1 : 0)
                + ($optionValue[1] < 200 ? 1 : 0);
            $this->assertGreaterThanOrEqual(1, $elementsBeingShrunk);
        }
    }
}
