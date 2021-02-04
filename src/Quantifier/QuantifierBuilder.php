<?php

declare(strict_types=1);

namespace Eris\Quantifier;

use Eris\Contracts\Listener;
use Eris\Contracts\Quantifier;
use Eris\Contracts\QuantifierConfiguration;
use Eris\Contracts\TerminationCondition;

use function is_int;

final class QuantifierBuilder implements QuantifierConfiguration
{
    /**
     * @var list<Listener> $listeners
     */
    private array $listeners = [];

    /**
     * @var list<TerminationCondition> $terminationConditions
     */
    private array $terminationConditions = [];

    private ?int $maximumIterations = null;

    private ?int $maximumSize = null;

    private bool $withoutShrinking = false;

    public function build(Quantifier $quantifier): Quantifier
    {
        foreach ($this->listeners as $listener) {
            $quantifier->listenTo($listener);
        }

        foreach ($this->terminationConditions as $terminationCondition) {
            $quantifier->stopOn($terminationCondition);
        }

        if (is_int($this->maximumIterations)) {
            $quantifier->withMaximumIterations($this->maximumIterations);
        }

        if (is_int($this->maximumSize)) {
            $quantifier->withMaximumSize($this->maximumSize);
        }

        if ($this->withoutShrinking) {
            $quantifier->withoutShrinking();
        }

        return $quantifier;
    }

    public function listenTo(Listener $listener): self
    {
        $this->listeners[] = $listener;

        return $this;
    }

    public function stopOn(TerminationCondition $terminationCondition): self
    {
        $this->terminationConditions[] = $terminationCondition;

        return $this;
    }

    public function withMaximumIterations(int $maximumIterations): self
    {
        $this->maximumIterations = $maximumIterations;

        return $this;
    }

    public function withMaximumSize(int $maximumSize): self
    {
        $this->maximumSize = $maximumSize;

        return $this;
    }

    public function withoutShrinking(): self
    {
        $this->withoutShrinking = true;

        return $this;
    }
}
