<?php

namespace Eris;

use Eris\Generator\GeneratedValue;

/**
 * @template T
 * @psalm-template T
 * Generic interface for a type <T>.
 */
interface Generator
{
    /**
     * @param int The generation size
     * @param Random\RandomRange $rand
     * @return GeneratedValue<T>
     */
    public function __invoke(int $size, Random\RandomRange $rand);

    /**
     * The conditions for terminating are either:
     * - returning the same GeneratedValueSingle passed in
     * - returning an empty GeneratedValueOptions
     *
     * @param GeneratedValue<T> $element
     * @return GeneratedValue<T>
     */
    public function shrink(GeneratedValue $element);
}
