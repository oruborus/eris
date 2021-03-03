<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;

function string(): StringGenerator
{
    return new StringGenerator();
}

class StringGenerator implements Generator
{
    public function __invoke(int $size, RandomRange $rand)
    {
        $length = $rand->rand(0, $size);

        $built = '';
        for ($i = 0; $i < $length; $i++) {
            $built .= chr($rand->rand(33, 126));
        }
        return GeneratedValueSingle::fromJustValue($built, 'string');
    }

    /**
     * @return GeneratedValue
     */
    public function shrink(GeneratedValue $element)
    {
        if ($element->unbox() === '') {
            return $element;
        }
        return GeneratedValueSingle::fromJustValue(
            substr($element->unbox(), 0, -1),
            'string'
        );
    }
}
