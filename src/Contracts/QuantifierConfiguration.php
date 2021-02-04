<?php

declare(strict_types=1);

namespace Eris\Contracts;

interface QuantifierConfiguration
{
    public function listenTo(Listener $listener): self;

    public function stopOn(TerminationCondition $terminationCondition): self;

    public function withMaximumIterations(int $maximumIterations): self;

    public function withMaximumSize(int $maximumSize): self;

    public function withoutShrinking(): self;
}
