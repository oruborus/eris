<?php

declare(strict_types=1);

namespace Eris\Generator;

/**
 * @template TInnerValue
 */
class TupleGenerator extends AssociativeArrayGenerator
{
    /**
     * @param list<Generator<TInnerValue>> $generators
     */
    public function __construct(array $generators)
    {
        parent::__construct(array_values($generators));
    }
}
