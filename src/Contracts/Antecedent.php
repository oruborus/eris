<?php

declare(strict_types=1);

namespace Eris\Contracts;

interface Antecedent
{
    /**
     * @param array<mixed> $values  all the values in a single shot
     */
    public function evaluate(array $values): bool;
}
