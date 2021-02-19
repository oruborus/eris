<?php

declare(strict_types=1);

namespace Eris\TerminationCondition;

use Eris\Contracts\Collection;
use Eris\Contracts\TerminationCondition;

/**
 * @extends Collection<TerminationCondition>
 */
class TerminationConditionCollection extends Collection implements TerminationCondition
{
    public function startPropertyVerification(): void
    {
        foreach ($this->elements as $terminationCondition) {
            $terminationCondition->startPropertyVerification();
        }
    }

    /**
     * @inheritdoc
     */
    public function shouldTerminate(): bool
    {
        foreach ($this->elements as $terminationCondition) {
            if ($terminationCondition->shouldTerminate()) {
                return true;
            }
        }

        return false;
    }
}
