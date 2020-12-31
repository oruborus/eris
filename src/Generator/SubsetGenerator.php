<?php

declare(strict_types=1);

namespace Eris\Generator;

// TODO: dependency on ForAll is bad,
// maybe inject the relative size?
use Eris\Quantifier\ForAll;
use Eris\Random\RandomRange;
use Eris\Contracts\Generator;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

class SubsetGenerator implements Generator
{
    private $universe;

    public function __construct(array $universe)
    {
        $this->universe = $universe;
    }

    /**
     * @return Value<array>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $relativeSize = $size / ForAll::DEFAULT_MAX_SIZE;
        $maximumSubsetIndex = (int) floor(pow(2, count($this->universe)) * $relativeSize);
        $subsetIndex = $rand->rand(0, $maximumSubsetIndex);
        $binaryDescription = str_pad(decbin($subsetIndex), count($this->universe), "0", STR_PAD_LEFT);
        $subset = [];
        for ($i = 0; $i < strlen($binaryDescription); $i++) {
            $elementPresent = $binaryDescription[$i];
            if ($elementPresent == "1") {
                $subset[] = $this->universe[$i];
            }
        }

        return new Value($subset);
    }

    /**
     * @param Value<array> $set
     * @return ValueCollection<array>
     */
    public function shrink(Value $set): ValueCollection
    {
        // TODO: see SetGenerator::shrink()
        if (count($set->unbox()) === 0) {
            return new ValueCollection([$set]);
        }

        $input = $set->input();
        // TODO: make deterministic by returning an array of Values
        $indexOfElementToRemove = array_rand($input);
        unset($input[$indexOfElementToRemove]);

        return new ValueCollection([new Value(array_values($input))]);
    }
}
