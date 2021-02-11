<?php

declare(strict_types=1);

namespace Eris\Antecedent;

use Eris\Contracts\Antecedent;

class AntecedentCollection implements Antecedent
{
    /**
     * @var Antecedent[] $antecedents
     */
    private array $antecedents = [];

    /**
     * @param Antecedent[] $antecedents
     */
    public function __construct(array $antecedents = [])
    {
        $this->antecedents = $antecedents;
    }

    public function add(Antecedent $antecedent): self
    {
        $this->antecedents[] = $antecedent;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function evaluate(array $values): bool
    {
        foreach ($this->antecedents as $antecedentToVerify) {
            if (!$antecedentToVerify->evaluate($values)) {
                return false;
            }
        }

        return true;
    }
}
