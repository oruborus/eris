<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use InvalidArgumentException;

class NamesGenerator implements Generator
{
    /**
     * @var string[] $list
     */
    private array $list;

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

    /**
     * @param string[] $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }

    /**
     * @return Value<string>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $candidateNames = $this->filterDataSet(
            $this->lengthLessThanOrEqualTo($size)
        );
        if (!$candidateNames) {
            return new Value('');
        }
        $index = $rand->rand(0, count($candidateNames) - 1);

        return new Value($candidateNames[$index]);
    }

    /**
     * @param Value<string> $element
     * @return ValueCollection<string>
     */
    public function shrink(Value $value): ValueCollection
    {
        $candidateNames = $this->filterDataSet(
            $this->lengthSlightlyLessThan(strlen($value->unbox()))
        );

        if (!$candidateNames) {
            return new ValueCollection([$value]);
        }
        $distances = $this->distancesBy($value->unbox(), $candidateNames);

        return new ValueCollection([new Value($this->minimumDistanceName($distances))]);
    }

    /**
     * @param callable(string):bool $predicate
     * @return string[]
     */
    private function filterDataSet($predicate): array
    {
        return array_values(array_filter(
            $this->list,
            $predicate
        ));
    }

    /**
     * @return callable(string):bool
     */
    private function lengthLessThanOrEqualTo(int $size)
    {
        return fn (string $name): bool => strlen($name) <= $size;
    }

    /**
     * @return callable(string):bool
     */
    private function lengthSlightlyLessThan(int $size): \Closure
    {
        $lowerLength = $size - 1;
        return fn (string $name): bool => strlen($name) === $lowerLength;
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
