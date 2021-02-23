<?php

declare(strict_types=1);

namespace Eris\Antecedent;

use Eris\Contracts\Antecedent;
use Eris\Contracts\Collection;

/**
 * @extends Collection<Antecedent>
 */
class AntecedentCollection extends Collection implements Antecedent
{
    /**
     * @inheritdoc
     */
    public function evaluate(array $values): bool
    {
        foreach ($this->elements as $antecedentToVerify) {
            if (!$antecedentToVerify->evaluate($values)) {
                return false;
            }
        }

        return true;
    }
}
