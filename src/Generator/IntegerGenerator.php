<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function array_values;
use function floor;

/**
 * @implements Generator<int>
 */
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
        $value = $rand->rand(0, $size) * ($rand->rand(0, 1) ? -1 : 1);

        $result = ($this->mapFn)($value);

        return new Value($result);
    }

    /**
     * @param Value<int> $element
     * @return ValueCollection<int>
     */
    public function shrink(Value $element): ValueCollection
    {
        /**
         * @var int $input
         */
        $input = $element->input();

        if ($input === 0) {
            return new ValueCollection([$element]);
        }

        $sign = 1;

        if ($input < 0) {
            $sign = -1;
            $input *= $sign;
        }
        $mapFn = fn (int $value): int => $sign * ($this->mapFn)($value);

        $options = [];
        $nextHalf = $input;

        while ($nextHalf = (int) ($nextHalf / 2)) {
            $value = $mapFn($input - $nextHalf);
            $options[$value] = new Value($value);
        }

        $options = array_values($options) ?: [new Value($mapFn($input - 1))];

        return new ValueCollection($options);
    }
}
