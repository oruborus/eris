<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;
use InvalidArgumentException;

function names(): NamesGenerator
{
    return NamesGenerator::defaultDataSet();
}

class NamesGenerator implements Generator
{
    private $list;

    /**
     * @link http://data.bfontaine.net/names/firstnames.txt
     *
     * @return self
     */
    public static function defaultDataSet(): self
    {
        return new self(
            array_map(
                function ($line) {
                    return trim($line, " \n");
                },
                file(__DIR__ . "/first_names.txt")
            )
        );
    }

    public function __construct(array $list)
    {
        $this->list = $list;
    }

    public function __invoke(int $size, RandomRange $rand)
    {
        $candidateNames = $this->filterDataSet(
            $this->lengthLessThanOrEqualTo($size)
        );
        if (!$candidateNames) {
            return GeneratedValueSingle::fromJustValue('', 'names');
        }
        $index = $rand->rand(0, count($candidateNames) - 1);
        return GeneratedValueSingle::fromJustValue($candidateNames[$index], 'names');
    }

    /**
     * @return GeneratedValue
     */
    public function shrink(GeneratedValue $value)
    {
        $candidateNames = $this->filterDataSet(
            $this->lengthSlightlyLessThan(strlen($value->unbox()))
        );

        if (!$candidateNames) {
            return $value;
        }
        $distances = $this->distancesBy($value->unbox(), $candidateNames);
        return GeneratedValueSingle::fromJustValue($this->minimumDistanceName($distances), 'names');
    }

    /**
     * @param callable(mixed, mixed=):scalar $predicate
     * @return array
     *
     * @psalm-return list<mixed>
     */
    private function filterDataSet($predicate): array
    {
        return array_values(array_filter(
            $this->list,
            $predicate
        ));
    }

    /**
     * @return \Closure
     *
     * @psalm-return \Closure(mixed):bool
     */
    private function lengthLessThanOrEqualTo(int $size): \Closure
    {
        return function ($name) use ($size) {
            return strlen($name) <= $size;
        };
    }

    /**
     * @return \Closure
     *
     * @psalm-return \Closure(mixed):bool
     */
    private function lengthSlightlyLessThan(int $size): \Closure
    {
        $lowerLength = $size - 1;
        return function ($name) use ($lowerLength) {
            return strlen($name) === $lowerLength;
        };
    }

    /**
     * @param string[] $candidateNames
     * @return int[]
     * @psalm-return array<array-key, int>
     */
    private function distancesBy(string $value, array $candidateNames): array
    {
        $distances = [];
        foreach ($candidateNames as $name) {
            $distances[$name] = levenshtein($value, $name);
        }
        return $distances;
    }

    /**
     * @return (int|string)
     *
     * @psalm-return array-key
     * @param int[] $distances
     */
    private function minimumDistanceName(array $distances)
    {
        if (empty($distances)) {
            throw new InvalidArgumentException('At least one distance must be provided');
        }
        $minimumDistance = min($distances);
        $candidatesWithEqualDistance = array_filter(
            $distances,
            function ($distance) use ($minimumDistance) {
                return $distance == $minimumDistance;
            }
        );
        return array_keys($candidatesWithEqualDistance)[0];
    }
}
