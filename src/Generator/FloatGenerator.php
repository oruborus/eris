<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

/**
 * @implements Generator<float>
 */
class FloatGenerator implements Generator
{
    /**
     * @return Value<float>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $numerator   = (float) $rand->rand(-1 * $size, $size);
        $denominator = (float) $rand->rand(-1 * $size, $size) ?: 1.0;

        return new Value($numerator / $denominator);
    }

    /**
     * @param Value<float> $element
     * @return ValueCollection<float>
     */
    public function shrink(Value $element): ValueCollection
    {
        $value = $element->value();

        if ($value < 0.0) {
            return new ValueCollection([new Value(min($value + 1.0, 0.0))]);
        }

        if ($value > 0.0) {
            return new ValueCollection([new Value(max($value - 1.0, 0.0))]);
        }

        return new ValueCollection([new Value(0.0)]);
    }
}
