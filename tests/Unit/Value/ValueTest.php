<?php

declare(strict_types=1);

namespace Test\Unit\Value;

use Eris\Value\Value;
use Generator;
use PHPUnit\Framework\TestCase;
use Stringable;

use function array_merge;
use function chr;
use function fclose;
use function fopen;
use function ord;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ValueTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Value\Value::__construct
     * @covers Eris\Value\Value::value
     * @covers Eris\Value\Value::input
     *
     * @dataProvider provideValuesOfDifferentTypes
     *
     * @param mixed $value
     */
    public function canBeCreatedWithoutInput($value): void
    {
        $dut = new Value($value);

        $this->assertSame($value, $dut->value());
        $this->assertSame($value, $dut->input());
    }

    /**
     * @psalm-suppress InvalidPassByReference
     */
    public function provideValuesOfDifferentTypes(): array
    {
        return [
            'int'      => [5],
            'float'    => [M_PI],
            'string'   => ['string'],
            'array'    => [[123 => 5, 'h',]],
            'object'   => [(object)[123 => 5, 'h',]],
            'null'     => [null],
            'resource' => [fclose(fopen(__FILE__, 'r'))],
        ];
    }

    /**
     * @test
     *
     * @covers Eris\Value\Value::__construct
     * @covers Eris\Value\Value::value
     * @covers Eris\Value\Value::input
     *
     * @dataProvider provideValueAndInputTupeOfDifferentTypes
     *
     * @param mixed $value
     * @param mixed $input
     */
    public function canBeCreatedWithInput($value, $input): void
    {
        $dut = new Value($value, $input);

        $this->assertSame($value, $dut->value());
        $this->assertSame($input, $dut->input());
    }

    /**
     * @psalm-suppress MixedAssignment
     */
    public function provideValueAndInputTupeOfDifferentTypes(): Generator
    {
        $set1 = $set2 = $this->provideValuesOfDifferentTypes();

        foreach ($set1 as $key1 => $value1) {
            foreach ($set2 as $key2 => $value2) {
                yield "{$key1} and {$key2}" => [$value1, $value2];
            }
        }
    }

    /**
     * @test
     *
     * @covers Eris\Value\Value::map
     *
     * @uses Eris\Value\Value::__construct
     * @uses Eris\Value\Value::input
     * @uses Eris\Value\Value::value
     *
     * @dataProvider provideArgumentsForMapWithDifferentTypes
     *
     * @template TValue
     * @param TValue $value
     * @param callable(TValue):TValue $testFn
     * @param TValue $expected
     */
    public function valueCanBeMapped($value, $testFn, $expected): void
    {
        $initial = new Value($value);

        $dut = $initial->map($testFn);

        $this->assertSame($expected, $dut->value());
        $this->assertSame($initial, $dut->input());
    }

    /**
     * Cases for object, null or resource are not provided as the map functions are most likely the
     * cause of occuring errors.
     */
    public function provideArgumentsForMapWithDifferentTypes(): array
    {
        return [
            'int'    => [3, fn (int $value): int => 2 * $value, 6],
            'float'  => [M_PI_2, fn (float $value): float => 4 * $value, M_PI + M_PI],
            'string' => ['A', fn (string $value): string => chr(ord($value) + 2), 'C'],
            'array'  => [[1, 'b'], fn (array $value): array => [$value[1], $value[0]], ['b', 1]],
        ];
    }

    /**
     * @test
     *
     * @covers Eris\Value\Value::merge
     *
     * @uses Eris\Value\Value::__construct
     * @uses Eris\Value\Value::input
     * @uses Eris\Value\Value::value
     *
     * @dataProvider provideArgumentsForMergeWithDifferentTypes
     *
     * @template T
     * @param T $value1
     * @param T $input
     * @param T $value2
     * @param T $input2
     * @param callable(T, T):T $testFn
     * @param T $expectedValue
     * @param T $expectedInput
     */
    public function valuesCanBeMerged(
        $value1,
        $input1,
        $value2,
        $input2,
        $testFn,
        $expectedValue,
        $expectedInput
    ): void {
        $initial1 = new Value($value1, $input1);
        $initial2 = new Value($value2, $input2);

        $dut = $initial1->merge($initial2, $testFn);

        $this->assertSame($expectedValue, $dut->value());
        $this->assertSame($expectedInput, $dut->input());
    }

    /**
     * Cases for object, null or resource are not provided as the merge functions are most likely the
     * cause of occuring errors.
     */
    public function provideArgumentsForMergeWithDifferentTypes(): array
    {
        return [
            'int'    => [
                3, 5, 4, 6,
                fn (int $left, int $right): int => $left + $right,
                7, 11
            ],
            'float'  => [
                M_PI, M_PI_2, M_PI, M_PI_2,
                fn (float $left, float $right): float => 2 * ($left + $right),
                4 * M_PI, 2 * M_PI
            ],
            'string' => [
                'A', 'C', 'b', 'd',
                fn (string $left, string $right): string => "{$left}TEST{$right}",
                'ATESTb', 'CTESTd'
            ],
            'array'  => [
                [1], [2], ['b'], ['a'],
                fn (array $left, array $right): array => array_merge($left, $right),
                [1, 'b'], [2, 'a']
            ],
        ];
    }

    /**
     * @test
     *
     * @covers Eris\Value\Value::__toString
     *
     * @uses Eris\Value\Value::__construct
     */
    public function hasStringRepresentation(): void
    {
        $expected = Value::class . "::__set_state(array(\n   'value' => 'a',\n   'input' => 3125,\n))";
        $initial  = new Value('a', 3125);

        $dut = (string) $initial;

        $this->assertInstanceOf(Stringable::class, $initial);
        $this->assertSame($expected, $dut);
    }
}
