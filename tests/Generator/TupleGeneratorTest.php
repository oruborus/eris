<?php

namespace Eris\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Value\Value;
use PHPUnit\Framework\TestCase;

class TupleGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->generatorForSingleElement = new ChooseGenerator(0, 100);
        $this->size = 10;
        $this->rand = new RandomRange(new RandSource());
    }

    private function assertInSingleElementGenerator($value)
    {
        $this->assertIsInt($value);
        $this->assertGreaterThanOrEqual(0, $value);
        $this->assertLessThanOrEqual(100, $value);
    }

    public function testConstructWithAnArrayOfGenerators()
    {
        $generator = new TupleGenerator([
            $this->generatorForSingleElement,
            $this->generatorForSingleElement,
        ]);

        $generated = $generator($this->size, $this->rand);

        $this->assertSame(2, count($generated->unbox()));
        foreach ($generated->unbox() as $element) {
            $this->assertInSingleElementGenerator($element);
        }
    }

    public function testConstructWithNonGenerators()
    {
        $aNonGenerator = 42;
        $generator = new TupleGenerator([$aNonGenerator]);

        $generated = $generator($this->size, $this->rand);

        foreach ($generated->unbox() as $element) {
            $this->assertEquals(42, $element);
        }
    }

    public function testConstructWithNoArguments()
    {
        $generator = new TupleGenerator([]);

        $this->assertSame([], $generator($this->size, $this->rand)->unbox());
    }

    public function testShrink()
    {
        $generator = new TupleGenerator([
            $this->generatorForSingleElement,
            $this->generatorForSingleElement,
        ]);

        $elements = $generator->__invoke($this->size, $this->rand);
        $elementsAfterShrink = $generator->shrink($elements);

        $this->assertInSingleElementGenerator($elementsAfterShrink->unbox()[0]);
        $this->assertInSingleElementGenerator($elementsAfterShrink->unbox()[1]);

        $this->assertLessThanOrEqual(
            $elements->unbox()[0] + $elements->unbox()[1],
            $elementsAfterShrink->unbox()[0] + $elementsAfterShrink->unbox()[1],
            var_export(
                [
                    'elements' => $elements,
                    'elementsAfterShrink' => $elementsAfterShrink,
                ],
                true
            )
        );
    }

    public function testDoesNotShrinkSomethingAlreadyShrunkToTheMax()
    {
        $constants = [42, 42];
        $generator = new TupleGenerator($constants);
        $elements = $generator($this->size, $this->rand);
        $this->assertSame($constants, $elements->unbox());
        $elementsAfterShrink = $generator->shrink($elements);
        $this->assertSame($constants, $elementsAfterShrink->unbox());
    }

    public function testShrinkingMultipleOptionsOfOneGenerator()
    {
        $generator = new TupleGenerator([
            new IntegerGenerator()
        ]);
        $value = new Value([100], [new Value(100)]);
        $shrunk = $generator->shrink($value);
        $this->assertGreaterThan(1, $shrunk->count());
        foreach ($shrunk as $option) {
            // $this->assertEquals('tuple', $option->generatorName());
            $optionValue = $option->unbox();
            $this->assertIsArray($optionValue);
            $this->assertEquals(1, count($optionValue));
        }
    }

    /**
     * @depends testShrinkingMultipleOptionsOfOneGenerator
     */
    public function testShrinkingMultipleOptionsOfMoreThanOneSingleShrinkingGenerator()
    {
        $generator = new TupleGenerator([
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
        $shrunk = $generator->shrink($value);
        // shrinking (a), (b) or (a and b)
        $this->assertEquals(3, $shrunk->count());
        foreach ($shrunk as $option) {
            // $this->assertEquals('tuple', $option->generatorName());
            $optionValue = $option->unbox();
            $this->assertIsArray($optionValue);
            $this->assertEquals(2, count($optionValue));
            $elementsBeingShrunk =
                (strlen($optionValue[0]) < 5 ? 1 : 0)
                + (strlen($optionValue[1]) < 5 ? 1 : 0);
            $this->assertGreaterThanOrEqual(1, $elementsBeingShrunk);
        }
    }

    /**
     * @depends testShrinkingMultipleOptionsOfOneGenerator
     */
    public function testShrinkingMultipleOptionsOfMoreThanOneMultipleShrinkingGenerator()
    {
        $generator = new TupleGenerator([
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
        $shrunk = $generator->shrink($value);
        $this->assertGreaterThan(1, $shrunk->count());
        foreach ($shrunk as $option) {
            // $this->assertEquals('tuple', $option->generatorName());
            $optionValue = $option->unbox();
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
