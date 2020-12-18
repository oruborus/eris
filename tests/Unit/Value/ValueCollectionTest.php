<?php

declare(strict_types=1);

namespace Test\Unit\Value;

use Eris\Value\ValueCollection;
use Eris\Value\Value;
use PHPUnit\Framework\TestCase;
use Stringable;

use function array_map;
use function array_merge;
use function iterator_to_array;
use function PHPUnit\Framework\assertSame;
use function rand;
use function restore_error_handler;
use function round;
use function set_error_handler;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ValueCollectionTest extends TestCase
{
    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::__construct
     * @covers Eris\Value\ValueCollection::count
     */
    public function canBeCreatedWithoutElements(): void
    {
        $dut = new ValueCollection();

        $this->assertCount(0, $dut);
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::__construct
     * @covers Eris\Value\ValueCollection::count
     *
     * @uses Eris\Value\Value
     */
    public function canBeCreatedWithRandomAmountOfElements(): void
    {
        $valueCount = rand(1, 25);
        $values = [];
        for ($i = 0; $i < $valueCount; $i++) {
            $values[] = new Value($valueCount);
        }

        $dut = new ValueCollection($values);

        $this->assertCount($valueCount, $dut);
    }

    /**
     * @test
     * @group unit
     * 
     * @covers Eris\Value\ValueCollection::getIterator
     *
     * @uses Eris\Value\ValueCollection::__construct
     *
     * @uses Eris\Value\Value
     */
    public function canBeIteratedOverWithForeach(): void
    {
        $valueCount = rand(1, 25);
        $values = [];
        for ($i = 0; $i < $valueCount; $i++) {
            $values[] = new Value($i);
        }

        $dut = new ValueCollection($values);

        foreach ($dut as $key => $value) {
            $this->assertSame($key, $value->value());
        }
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::offsetSet
     * @covers Eris\Value\ValueCollection::offsetGet
     *
     * @uses Eris\Value\ValueCollection::__construct
     *
     * @uses Eris\Value\Value
     */
    public function elementsCanBeAccessedWithArrayAccessNotation(): void
    {
        $valueCount = rand(1, 25);
        $key = rand(0, $valueCount - 1);
        $specificValue = new Value(123);

        $dut = new ValueCollection();

        for ($i = 0; $i < $valueCount; $i++) {
            $dut[] = new Value($i);
        }

        $dut['specific-offset'] = $specificValue;

        /**
         * @psalm-suppress PossiblyNullReference
         */
        $this->assertSame($key, $dut[$key]->value());
        $this->assertSame($specificValue, $dut['specific-offset']);
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::offsetGet
     *
     * @uses Eris\Value\ValueCollection::__construct
     */
    public function triggersWarningWhenNonexistingOffsetIsAccessed(): void
    {
        $this->expectWarning();
        $this->expectWarningMessageMatches('/Undefined ValueCollection key non-existing in .+ on line \d+/');

        $dut = new ValueCollection();

        $dut['non-existing'];
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::offsetGet
     *
     * @uses Eris\Value\ValueCollection::__construct
     */
    public function returnsNullWhenNonexistingOffsetIsAccessed(): void
    {
        /**
         * @psalm-suppress InvalidArgument
         */
        set_error_handler(fn () => null);
        $dut = new ValueCollection();

        $this->assertNull($dut['non-existing']);

        restore_error_handler();
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::offsetExists
     * @covers Eris\Value\ValueCollection::offsetUnset
     *
     * @uses Eris\Value\ValueCollection::__construct
     * 
     * @uses Eris\Value\Value
     */
    public function elementsCanBeRemovedByOffset(): void
    {
        $dut = new ValueCollection([new Value(1), new Value(2), new Value(3)]);

        $this->assertTrue(isset($dut[1]));

        unset($dut[1]);

        $this->assertFalse(isset($dut[1]));
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::__toString
     *
     * @uses Eris\Value\ValueCollection::__construct
     *
     * @uses Eris\Value\Value
     */
    public function hasStringRepresentation(): void
    {
        $initial  = new ValueCollection([new Value('a', 3125)]);
        $expected = Value::class .
            "::__set_state(array(\n       'value' => 'a',\n       'input' => 3125,\n    )),";
        $expected = ValueCollection::class .
            "::__set_state(array(\n   'values' => \n  array (\n    0 => \n    {$expected}\n  ),\n))";

        $dut = (string) $initial;

        $this->assertInstanceOf(Stringable::class, $initial);
        $this->assertSame($expected, $dut);
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::map
     *
     * @uses Eris\Value\ValueCollection::__construct
     * @uses Eris\Value\ValueCollection::getIterator
     *
     * @uses Eris\Value\Value
     *
     * @dataProvider provideArgumentsForMapWithDifferentTypes
     *
     * @template TValue
     * @param array<array-key, Value<TValue>> $values
     * @param callable(TValue):TValue $mapTestFn
     * @param array<array-key, TValue> $expected
     */
    public function mappingOverAllValues(array $values, $mapTestFn, array $expected): void
    {
        $initial = new ValueCollection($values);
        $unwrapper =
            /**
             * @param Value<TValue> $value
             * @return TValue
             */
            fn (Value $value) => $value->value();

        $dut = $initial->map($mapTestFn);

        foreach ($dut as $key => $value) {
            $this->assertSame($values[$key], $value->input());
        }

        $this->assertSame($expected, array_map($unwrapper, iterator_to_array($dut)));
    }

    /**
     * Cases for object, null or resource are not provided as the map functions are most likely the
     * cause of occuring errors.
     */
    public function provideArgumentsForMapWithDifferentTypes(): array
    {
        return [
            'int' => [
                [new Value(1), new Value(2), new Value(3)],
                fn (int $value): int => 2 * $value,
                [2, 4, 6]
            ],
            'float' => [
                [new Value(M_PI_2), new Value(M_PI_4), new Value(M_PI)],
                fn (float $value): float => 2 * $value,
                [M_PI, M_PI_2, M_PI + M_PI]
            ],
            'string' => [
                [new Value('A'), new Value('B'), new Value('C')],
                fn (string $value): string => "__{$value}__",
                ['__A__', '__B__', '__C__']
            ],
            'array' => [
                [new Value(['A']), new Value(['B']), new Value(['C'])],
                fn (array $value): array => array_merge($value, ['D']),
                [['A', 'D'], ['B', 'D'], ['C', 'D']]
            ],
        ];
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::cartesianProduct
     *
     * @uses Eris\Value\ValueCollection::__construct
     * @uses Eris\Value\ValueCollection::getIterator
     * @uses Eris\Value\ValueCollection::offsetSet
     *
     * @uses Eris\Value\Value
     *
     * @dataProvider provideArgumentsForCartesianProductWithDifferentTypes
     *
     * @template TValue
     * @param array<array-key, Value<TValue>> $values1
     * @param array<array-key, Value<TValue>> $values2
     * @param callable(TValue):TValue $productTestFn
     * @param array<array-key, TValue> $expected
     */
    public function mergingAllValueCombination(array $values1, array $values2, $productTestFn, array $expected): void
    {
        $initial1 = new ValueCollection($values1);
        $initial2 = new ValueCollection($values2);
        $unwrapper =
            /**
             * @param Value<TValue> $value
             * @return TValue
             */
            fn (Value $value) => $value->value();

        $dut = $initial1->cartesianProduct($initial2, $productTestFn);

        $this->assertSame($expected, array_map($unwrapper, iterator_to_array($dut)));
    }

    /**
     * Cases for object, null or resource are not provided as the cartesianProduct functions are most likely the
     * cause of occuring errors.
     */
    public function provideArgumentsForCartesianProductWithDifferentTypes(): array
    {
        return [
            'int' => [
                [new Value(1), new Value(2), new Value(3)],
                [new Value(4), new Value(5), new Value(6)],
                fn (int $value1, int $value2): int => $value1 * $value2,
                [4, 5, 6, 8, 10, 12, 12, 15, 18]
            ],
            'float' => [
                [new Value(1.1), new Value(1.2), new Value(1.3)],
                [new Value(1.4), new Value(1.5), new Value(1.6)],
                fn (float $value1, float $value2): float => round($value1 + $value2, 1),
                [2.5, 2.6, 2.7, 2.6, 2.7, 2.8, 2.7, 2.8, 2.9]
            ],
            'string' => [
                [new Value('A'), new Value('B'), new Value('C')],
                [new Value('D'), new Value('E'), new Value('F')],
                fn (string $value1, string $value2): string => $value1 . $value2,
                ['AD', 'AE', 'AF', 'BD', 'BE', 'BF', 'CD', 'CE', 'CF']
            ],
            'array' => [
                [new Value(['A']), new Value(['B']), new Value(['C'])],
                [new Value(['D']), new Value(['E']), new Value(['F'])],
                fn (array $value1, array $value2): array => array_merge($value1, $value2),
                [
                    ['A', 'D'], ['A', 'E'], ['A', 'F'],
                    ['B', 'D'], ['B', 'E'], ['B', 'F'],
                    ['C', 'D'], ['C', 'E'], ['C', 'F'],
                ]
            ],
        ];
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::first
     *
     * @uses Eris\Value\ValueCollection::__construct
     * @uses Eris\Value\ValueCollection::offsetSet
     *
     * @uses Eris\Value\Value
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function triggerWarningWhenFirstElementOfEmptyCollectionIsRequested(): void
    {
        $this->expectWarning();
        $this->expectWarningMessageMatches('/Undefined first element of ValueCollection in .+ on line \d+/');

        $dut = new ValueCollection();

        $dut->first();
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::first
     *
     * @uses Eris\Value\ValueCollection::__construct
     * @uses Eris\Value\ValueCollection::offsetSet
     *
     * @uses Eris\Value\Value
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function canReturnTheFirstElement(): void
    {
        /**
         * @psalm-suppress InvalidArgument
         */
        set_error_handler(fn () => null);

        $expected = new Value(0);
        $valueCount = rand(1, 25);

        $dut = new ValueCollection();

        $this->assertNull($dut->first());

        $dut[] = $expected;

        for ($i = 1; $i <= $valueCount; $i++) {
            $dut[] = new Value($i);
        }

        $this->assertSame($expected, $dut->first());

        restore_error_handler();
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::last
     *
     * @uses Eris\Value\ValueCollection::__construct
     * @uses Eris\Value\ValueCollection::offsetSet
     *
     * @uses Eris\Value\Value
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function triggerWarningWhenLastElementOfEmptyCollectionIsRequested(): void
    {
        $this->expectWarning();
        $this->expectWarningMessageMatches('/Undefined last element of ValueCollection in .+ on line \d+/');

        $dut = new ValueCollection();

        $dut->last();
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::last
     *
     * @uses Eris\Value\ValueCollection::__construct
     * @uses Eris\Value\ValueCollection::offsetSet
     *
     * @uses Eris\Value\Value
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function canReturnTheLastElement(): void
    {
        /**
         * @psalm-suppress InvalidArgument
         */
        set_error_handler(fn () => null);

        $expected = new Value(0);
        $valueCount = rand(1, 25);

        $dut = new ValueCollection();

        $this->assertNull($dut->last());

        for ($i = 1; $i <= $valueCount; $i++) {
            $dut[] = new Value($i);
        }

        $dut[] = $expected;

        $this->assertSame($expected, $dut->last());

        restore_error_handler();
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::add
     *
     * @uses Eris\Value\ValueCollection::__construct
     * @uses Eris\Value\ValueCollection::count
     * @uses Eris\Value\ValueCollection::offsetGet
     * @uses Eris\Value\ValueCollection::offsetSet
     *
     * @uses Eris\Value\Value
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function elementsCanBeAdded(): void
    {
        $initial = new ValueCollection();
        $expected = new Value(5);

        $dut = $initial->add($expected);

        $this->assertCount(1, $dut);
        $this->assertSame($expected, $dut[0]);
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::remove
     *
     * @uses Eris\Value\ValueCollection::__construct
     * @uses Eris\Value\ValueCollection::count
     * @uses Eris\Value\ValueCollection::offsetGet
     * @uses Eris\Value\ValueCollection::offsetUnset
     *
     * @uses Eris\Value\Value
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function elementsCanBeRemoved(): void
    {
        $expected = new Value('to-be-removed');
        $initial = new ValueCollection([$expected]);

        $dut = $initial->remove($expected);

        $this->assertCount(0, $dut);
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::unbox
     *
     * @uses Eris\Value\ValueCollection::__construct
     * @uses Eris\Value\ValueCollection::last
     *
     * @uses Eris\Value\Value
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function returnsValueOfLastElement(): void
    {
        $expected = 5;
        $dut = new ValueCollection([new Value(1), new Value(2), new Value(3), new Value(4), new Value($expected)]);

        $this->assertSame($expected, $dut->unbox());
    }

    /**
     * @test
     * @group unit
     *
     * @covers Eris\Value\ValueCollection::input
     *
     * @uses Eris\Value\ValueCollection::__construct
     * @uses Eris\Value\ValueCollection::last
     *
     * @uses Eris\Value\Value
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function returnsInputOfLastElement(): void
    {
        $expected = 5;
        $dut = new ValueCollection([new Value(1), new Value(2), new Value(3), new Value(4), new Value($expected)]);

        $this->assertSame($expected, $dut->input());
    }
}
