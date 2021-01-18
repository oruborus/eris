<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function count;

/**
 * @implements Generator<bool>
 */
class BooleanGenerator implements Generator
{
    /**
     * @return Value<bool>
     */
    public function __invoke(int $_size, RandomRange $rand): Value
    {
        $booleanValues = [true, false];
        $randomIndex = $rand->rand(0, count($booleanValues) - 1);

        return new Value($booleanValues[$randomIndex]);
    }

    /**
     * @param Value<bool> $element
     * @return ValueCollection<bool>
     */
    public function shrink(Value $element): ValueCollection
    {
        return new ValueCollection([new Value(false)]);
    }
}
