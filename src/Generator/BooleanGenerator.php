<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;

function bool(): BooleanGenerator
{
    return new BooleanGenerator();
}

class BooleanGenerator implements Generator
{
    public function __invoke(int $_size, RandomRange $rand)
    {
        $booleanValues = [true, false];
        $randomIndex = $rand->rand(0, count($booleanValues) - 1);

        return GeneratedValueSingle::fromJustValue($booleanValues[$randomIndex], 'boolean');
    }

    /**
     * @return GeneratedValueSingle
     */
    public function shrink(GeneratedValue $element)
    {
        return GeneratedValueSingle::fromJustValue(false);
    }
}
