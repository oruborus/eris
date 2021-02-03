<?php

declare(strict_types=1);

namespace Eris\Antecedent;

use Eris\Contracts\Antecedent;
use PHPUnit\Framework\Constraint\Constraint;

class IndependentConstraintsAntecedent implements Antecedent
{
    /**
     * @var Constraint[] $constraints
     */
    private $constraints;

    /**
     * @param Constraint[] $constraints
     */
    public function __construct($constraints)
    {
        $this->constraints = $constraints;
    }

    public function evaluate(array $values): bool
    {
        foreach ($this->constraints as $key => $constraint) {
            if (!$constraint->evaluate($values[$key], '', true)) {
                return false;
            }
        }

        return true;
    }
}
