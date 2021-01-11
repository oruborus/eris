<?php

declare(strict_types=1);

namespace Eris;

use Generator;

use function array_key_last;
use function array_pop;

/**
 * This function generates the cartesian product of two or more arrays.
 *
 * NOTICE:
 * This is a recursive function, which means that the call stack depth
 * rises with every element of the parameter $array by one:
 *
 *     $stackDepth = \count($array);
 *
 * The count of possible result elements is calculated as follows:
 *
 *     $count = \array_product(
 *         \array_map(fn (array $element): int => \count($element), $array)
 *     );
 *
 * @template TValue
 * @param TValue[][] $array
 * @return Generator<array-key, TValue[], mixed, void>
 */
function cartesianProduct(array $array): Generator
{
    if (empty($array)) {
        yield [];
        return;
    }

    $key  = array_key_last($array);
    $unit = array_pop($array);

    foreach (cartesianProduct($array) as $part) {
        foreach ($unit as $value) {
            yield $part + [$key => $value];
        }
    }
}
