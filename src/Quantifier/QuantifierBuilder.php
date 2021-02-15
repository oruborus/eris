<?php

declare(strict_types=1);

namespace Eris\Quantifier;

use DateInterval;
use Eris\Contracts\Listener;
use Eris\Contracts\Quantifier;
use Eris\Contracts\QuantifierConfiguration;
use Eris\Contracts\TerminationCondition;
use Eris\TerminationCondition\TimeBasedTerminationCondition;

use function is_int;
use function is_string;

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

    /**
     * @var null|string|class-string<Growth> $growth
     */
    private ?string $growth = null;

    private ?int $maximumIterations = null;

    private ?int $maximumSize = null;

    private ?int $seed = null;

    private bool $withoutShrinking = false;

    /**
     * @var null|string|class-string<Source> $source
     */
    private ?string $source = null;

    private ?int $shrinkingTimeLimit = null;

    /**
     * @template TQuantifier of Quantifier
     * @param TQuantifier $quantifier
     * @return TQuantifier
     */
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

        if (is_string($this->growth)) {
            $quantifier->withGrowth($this->growth);
        }

        if (is_int($this->maximumSize)) {
            $quantifier->withMaximumSize($this->maximumSize);
        }

        if ($this->withoutShrinking) {
            $quantifier->withoutShrinking();
        }

        if (is_string($this->source)) {
            $quantifier->withRand($this->source);
        }

        if (is_int($this->seed)) {
            $quantifier->withSeed($this->seed);
        }

        if (is_int($this->shrinkingTimeLimit)) {
            $quantifier->withShrinkingTimeLimit($this->shrinkingTimeLimit);
        }

        return $quantifier;
    }

    public function limitTo(int|DateInterval $limit): self
    {
        if ($limit instanceof DateInterval) {
            $this->terminationConditions[] = new TimeBasedTerminationCondition('time', $limit);

            return $this;
        }

        $this->maximumIterations = $limit;

        return $this;
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

    /**
     * @param string|class-string<Growth> $growth
     */
    public function withGrowth(string $growth): self
    {
        $this->growth = $growth;

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

    public function withSeed(int $seed): self
    {
        $this->seed = $seed;

        return $this;
    }

    public function withoutShrinking(): self
    {
        $this->withoutShrinking = true;

        return $this;
    }

    /**
     * @param string|class-string<Source> $source
     */
    public function withRand(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function withShrinkingTimeLimit(int $shrinkingTimeLimit): self
    {
        $this->shrinkingTimeLimit = $shrinkingTimeLimit;

        return $this;
    }
}
