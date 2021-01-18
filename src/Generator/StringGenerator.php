<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function chr;
use function mb_substr;

/**
 * @implements Generator<string>
 */
class StringGenerator implements Generator
{
    /**
     * @return Value<string>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $length = $rand->rand(0, $size);

        $built = '';
        for ($i = 0; $i < $length; $i++) {
            $built .= chr($rand->rand(33, 126));
        }
        return new Value($built);
    }

    /**
     * @param Value<string> $element
     * @return ValueCollection<string>
     */
    public function shrink(Value $element): ValueCollection
    {
        $value = $element->value();

        if (empty($value)) {
            return new ValueCollection([$element]);
        }
        return new ValueCollection([new Value(mb_substr($element->value(), 0, -1))]);
    }
}
