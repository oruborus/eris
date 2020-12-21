<?php

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

/**
 * @return OneOfGenerator
 */
function oneOf(/*$a, $b, ...*/)
{
    return new OneOfGenerator(func_get_args());
}

class OneOfGenerator implements Generator
{
    private FrequencyGenerator $generator;

    /**
     * @param Generator[] $generators
     */
    public function __construct($generators)
    {
        $this->generator = new FrequencyGenerator($this->allWithSameFrequency($generators));
    }

    public function __invoke(int $size, RandomRange $rand): Value
    {
        return $this->generator->__invoke($size, $rand);
    }

    public function shrink(Value $element): ValueCollection
    {
        return $this->generator->shrink($element);
    }

    /**
     * @param Generator[] $generators
     * @return (int|Generator)[][]
     * @psalm-return array<array-key, array{0: int, 1: Generator}>
     */
    private function allWithSameFrequency(array $generators): array
    {
        return array_map(
            function ($generator): array {
                return [1, $generator];
            },
            $generators
        );
    }
}
