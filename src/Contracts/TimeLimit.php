<?php

declare(strict_types=1);

namespace Eris\Contracts;

use Stringable;

interface TimeLimit extends Stringable
{
    /**
     * Call to start measuring the time interval.
     */
    public function start(): void;

    public function hasBeenReached(): bool;

    public function __toString(): string;
}
