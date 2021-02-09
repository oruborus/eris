<?php

declare(strict_types=1);

namespace Eris\Contracts;

use DateInterval;

interface QuantifierConfiguration
{
    public function limitTo(int|DateInterval $limit): self;

    public function listenTo(Listener $listener): self;

    public function stopOn(TerminationCondition $terminationCondition): self;

    /**
     * @param string|class-string<Growth> $source
     */
    public function withGrowth(string $growth): self;

    public function withMaximumIterations(int $maximumIterations): self;

    public function withMaximumSize(int $maximumSize): self;

    public function withoutShrinking(): self;

    /**
     * @param string|class-string<Source> $source
     */
    public function withRand(string $source): self;

    public function withShrinkingTimeLimit(int $shrinkingTimeLimit): self;
}
