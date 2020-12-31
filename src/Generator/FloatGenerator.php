<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

class FloatGenerator implements Generator
{
    public function __construct()
    {
    }

    /**
     * @return Value<float>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $denominator = $rand->rand(1, $size) ?: 1;

        $value = (float) $rand->rand(0, $size) / (float) $denominator;

        $signedValue = $rand->rand(0, 1) === 0
            ? $value
            : $value * (-1);

        return new Value($signedValue);
    }

    /**
     * @param Value<float> $element
     * @return ValueCollection<float>
     */
    public function shrink(Value $element): ValueCollection
    {
        $value = $element->unbox();

        if ($value < 0.0) {
            return new ValueCollection([new Value(min($value + 1.0, 0.0), 'float')]);
        }

        if ($value > 0.0) {
            return new ValueCollection([new Value(max($value - 1.0, 0.0), 'float')]);
        }

        return new ValueCollection([new Value(0.0)]);
    }
}
