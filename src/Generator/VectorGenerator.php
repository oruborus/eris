<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;

use function array_fill;

/**
 * @inheritdoc
 */
class VectorGenerator extends TupleGenerator
{
    /**
     * @inheritdoc
     */
    public function __construct(int $size, Generator $generator)
    {
        parent::__construct(array_fill(0, $size, $generator));
    }
}
