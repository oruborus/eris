<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;

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
        if (is_null($mapFn)) {
            $this->mapFn = $this->identity();
        } else {
            $this->mapFn = $mapFn;
        }
    }

    public function __invoke(int $size, RandomRange $rand)
    {
        $value = $rand->rand(0, $size);
        $mapFn = $this->mapFn;

        $result = $rand->rand(0, 1) === 0
            ? $mapFn($value)
            : $mapFn($value * (-1));
        return GeneratedValueSingle::fromJustValue(
            $result,
            'integer'
        );
    }

    /**
     * @return GeneratedValueOptions|GeneratedValueSingle
     */
    public function shrink(GeneratedValue $element)
    {
        $mapFn = $this->mapFn;
        $element = $element->input();

        if ($element > 0) {
            $options = [];
            $nextHalf = $element;
            while (($nextHalf = (int) floor($nextHalf / 2)) > 0) {
                $options[] = GeneratedValueSingle::fromJustValue(
                    $mapFn($element - $nextHalf),
                    'integer'
                );
            }
            $options = array_unique($options, SORT_REGULAR);
            if ($options) {
                return new GeneratedValueOptions($options);
            } else {
                return GeneratedValueSingle::fromJustValue($mapFn($element - 1), 'integer');
            }
        }
        if ($element < 0) {
            // TODO: shrink with options also negative values
            return GeneratedValueSingle::fromJustValue($mapFn($element + 1), 'integer');
        }

        return GeneratedValueSingle::fromJustValue($element, 'integer');
    }

    /**
     * @return callable(int):int
     */
    private function identity()
    {
        return function (int $n): int {
            return $n;
        };
    }
}
