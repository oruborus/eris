<?php

declare(strict_types=1);

namespace Eris\Contracts;

use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

/**
 * @template TValue
 * Generic interface for a type <TValue>.
 */
interface Generator
{
    /**
     * @param int The generation size
     * @param RandomRange $rand
     * @return Value<TValue>
     */
    public function __invoke(int $size, RandomRange $rand): Value;

    /**
     * The conditions for terminating are either:
     * - returning the same Value passed in
     * - returning an empty ValueCollection
     *
     * @param Value<TValue> $element
     * @return ValueCollection<TValue>
     */
    public function shrink(Value $element): ValueCollection;
}
