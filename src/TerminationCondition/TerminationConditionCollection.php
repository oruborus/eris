<?php

declare(strict_types=1);

namespace Eris\TerminationCondition;

use Eris\Contracts\TerminationCondition;

class TerminationConditionCollection implements TerminationCondition
{
    /**
     * @var TerminationCondition[] $terminationConditions
     */
    private array $terminationConditions = [];

    /**
     * @param TerminationCondition[] $terminationConditions
     */
    public function __construct(array $terminationConditions = [])
    {
        $this->terminationConditions = $terminationConditions;
    }

    public function add(TerminationCondition $terminationCondition): self
    {
        $this->terminationConditions[] = $terminationCondition;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function shouldTerminate(): bool
    {
        foreach ($this->terminationConditions as $terminationCondition) {
            if ($terminationCondition->shouldTerminate()) {
                return true;
            }
        }

        return false;
    }
}
