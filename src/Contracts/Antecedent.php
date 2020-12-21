<?php

declare(strict_types=1);

namespace Eris\Contracts;

interface Antecedent
{
    /**
     * @param array $values  all the values in a single shot
     * @return boolean
     */
    public function evaluate(array $values);
}
