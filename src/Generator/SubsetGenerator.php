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

use function array_rand;
use function array_values;
use function array_splice;
use function count;
use function floor;

/**
 * @implements Generator<list<mixed>>
 */
class SubsetGenerator implements Generator
{
    /**
     * @var list<mixed> $universe
     */
    private array $universe;

    public function __construct(array $universe)
    {
        $this->universe = array_values($universe);
    }

    /**
     * @return Value<list<mixed>>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $universeSize = count($this->universe);
        $relativeSize = $size / ForAll::DEFAULT_MAX_SIZE;

        $maximumSubsetIndex = (int) floor(2 ** $universeSize * $relativeSize);
        $subsetIndex = $rand->rand(0, $maximumSubsetIndex);

        $subSet = [];
        for ($i = 0; $i < $universeSize; $i++) {
            if ($subsetIndex & (1 << $i)) {
                /**
                 * @var mixed
                 */
                $subSet[] = $this->universe[$i];
            }
        }

        return new Value($subSet);
    }

    /**
     * @param Value<list<mixed>> $set
     * @return ValueCollection<list<mixed>>
     */
    public function shrink(Value $set): ValueCollection
    {
        /**
         * @var list<mixed> $input
         */
        $input = $set->input();

        // TODO: see SetGenerator::shrink()
        if (empty($input)) {
            return new ValueCollection([$set]);
        }

        // TODO: make deterministic by returning an array of Values
        array_splice($input, array_rand($input), 1);

        /**
         * @var ValueCollection<list<mixed>>
         */
        return new ValueCollection([new Value($input)]);
    }
}
