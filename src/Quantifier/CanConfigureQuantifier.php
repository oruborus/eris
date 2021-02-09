<?php

declare(strict_types=1);

namespace Eris\Quantifier;

use DateInterval;
use Eris\Contracts\Growth;
use Eris\Contracts\Listener;
use Eris\Contracts\Source;
use Eris\Contracts\TerminationCondition;
use Eris\Quantifier\QuantifierBuilder;

trait CanConfigureQuantifier
{
    private ?QuantifierBuilder $quantifierBuilder = null;

    private function getQuantifierBuilder(): QuantifierBuilder
    {
        if (is_null($this->quantifierBuilder)) {
            $this->quantifierBuilder = new QuantifierBuilder();
        }

        return $this->quantifierBuilder;
    }

    private function limitTo(int|DateInterval $limit): static
    {
        $this->getQuantifierBuilder()->limitTo($limit);

        return $this;
    }

    private function listenTo(Listener $listener): static
    {
        $this->getQuantifierBuilder()->listenTo($listener);

        return $this;
    }

    private function stopOn(TerminationCondition $terminationCondition): static
    {
        $this->getQuantifierBuilder()->stopOn($terminationCondition);

        return $this;
    }

    /**
     * @param string|class-string<Growth> $growth
     */
    private function withGrowth(string $growth): static
    {
        $this->getQuantifierBuilder()->withGrowth($growth);

        return $this;
    }

    private function withMaximumIterations(int $maximumIterations): static
    {
        $this->getQuantifierBuilder()->withMaximumIterations($maximumIterations);

        return $this;
    }

    private function withMaximumSize(int $maximumSize): static
    {
        $this->getQuantifierBuilder()->withMaximumSize($maximumSize);

        return $this;
    }

    private function withoutShrinking(): static
    {
        $this->getQuantifierBuilder()->withoutShrinking();

        return $this;
    }

    /**
     * @param string|class-string<Source> $source
     */
    private function withRand(string $source): static
    {
        $this->getQuantifierBuilder()->withRand($source);

        return $this;
    }

    private function withShrinkingTimeLimit(int $shrinkingTimeLimit): static
    {
        $this->getQuantifierBuilder()->withShrinkingTimeLimit($shrinkingTimeLimit);

        return $this;
    }
}
