<?php

declare(strict_types=1);

namespace Eris\Generator;

use function array_map;

class OneOfGenerator extends FrequencyGenerator
{
    /**
     * @param list<mixed> $generators
     */
    public function __construct($generators)
    {
        $generatorWithFrequency = array_map(static fn ($item): array => [1, $item], $generators);

        parent::__construct($generatorWithFrequency);
    }
}
