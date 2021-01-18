<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;

use function array_fill;

/**
 * @template TInnerValue
 */
class VectorGenerator extends TupleGenerator
{
    /**
     * @param Generator<TInnerValue> $generator
     */
    public function __construct(int $size, Generator $generator)
    {
        parent::__construct(array_fill(0, $size, $generator));
    }
}
