<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

/**
 * Generates a positive or negative integer (with absolute value bounded by
 * the generation size).
 *
 * @return IntegerGenerator
 */
function int(): IntegerGenerator
{
    return new IntegerGenerator();
}

/**
 * Generates a positive integer (bounded by the generation size).
 *
 * @return IntegerGenerator
 */
function pos(): IntegerGenerator
{
    $mustBeStrictlyPositive = function (int $n): float {
        return abs($n) + 1;
    };
    return new IntegerGenerator($mustBeStrictlyPositive);
}

function nat(): IntegerGenerator
{
    $mustBeNatural = function (int $n): int {
        return abs($n);
    };
    return new IntegerGenerator($mustBeNatural);
}

/**
 * Generates a negative integer (bounded by the generation size).
 *
 * @return IntegerGenerator
 */
function neg(): IntegerGenerator
{
    $mustBeStrictlyNegative = function (int $n): int {
        return (-1) * (abs($n) + 1);
    };
    return new IntegerGenerator($mustBeStrictlyNegative);
}

function byte(): ChooseGenerator
{
    return new ChooseGenerator(0, 255);
}

class IntegerGenerator implements Generator
{
    /**
     * @var callable(int):int $mapFn
     */
    private $mapFn;

    /**
     * @param ?callable(int):int $mapFn
     */
    public function __construct($mapFn = null)
    {
        $this->mapFn = $mapFn ?? fn (int $n): int => $n;
    }

    /**
     * @return Value<int>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $value = $rand->rand(0, $size);
        $mapFn = $this->mapFn;

        $result = $rand->rand(0, 1) === 0
            ? $mapFn($value)
            : $mapFn($value * (-1));

        return new Value($result);
    }

    /**
     * @param Value<int> $element
     * @return ValueCollection<int>
     */
    public function shrink(Value $element): ValueCollection
    {
        $mapFn = $this->mapFn;
        $element = $element->input();

        if ($element > 0) {
            $options = [];
            $nextHalf = $element;
            while (($nextHalf = (int) floor($nextHalf / 2)) > 0) {
                $options[] = new Value($mapFn($element - $nextHalf));
            }
            $options = array_unique($options, SORT_REGULAR);
            if ($options) {
                return new ValueCollection($options);
            } else {
                return new ValueCollection([new Value($mapFn($element - 1))]);
            }
        }
        if ($element < 0) {
            // TODO: shrink with options also negative values
            return new ValueCollection([new Value($mapFn($element + 1))]);
        }

        return new ValueCollection([new Value($element)]);
    }
}
