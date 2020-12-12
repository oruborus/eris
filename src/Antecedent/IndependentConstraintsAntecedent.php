<?php

namespace Eris\Antecedent;

use PHPUnit\Framework\ExpectationFailedException;
use Eris\Antecedent;
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
    public static function fromAll(array $constraints): self
    {
        return new self($constraints);
    }

    /**
     * @param Constraint[] $constraints
     */
    private function __construct($constraints)
    {
        $this->constraints = $constraints;
    }

    public function evaluate(array $values)
    {
        for ($i = 0; $i < count($this->constraints); $i++) {
            // TODO: use Evaluation object?
            try {
                $this->constraints[$i]->evaluate($values[$i]);
            } catch (ExpectationFailedException $e) {
                return false;
            }
        }
        return true;
    }
}
