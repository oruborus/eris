<?php

declare(strict_types=1);

namespace Eris\Generator;

class TupleGenerator extends AssociativeArrayGenerator
{
    public function __construct(array $generators)
    {
        parent::__construct(array_values($generators));
    }
}
