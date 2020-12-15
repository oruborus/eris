<?php

namespace Eris;

use Eris\Generator\GeneratedValue;

/**
 * @template TValue
 * @psalm-template TValue
 * Generic interface for a type <TValue>.
 */
interface Generator
{
    /**
     * @param int The generation size
     * @param Random\RandomRange $rand
     * @return GeneratedValue<TValue>
     */
    public function __invoke(int $size, Random\RandomRange $rand);

    /**
     * The conditions for terminating are either:
     * - returning the same GeneratedValueSingle passed in
     * - returning an empty GeneratedValueOptions
     *
     * @param GeneratedValue<TValue> $element
     * @return GeneratedValue<TValue>
     */
    public function shrink(GeneratedValue $element);
}
