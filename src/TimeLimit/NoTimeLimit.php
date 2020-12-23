<?php

declare(strict_types=1);

namespace Eris\TimeLimit;

use Eris\Contracts\TimeLimit;

class NoTimeLimit implements TimeLimit
{
    public function start(): void
    {
    }

    /**
     * @return false
     */
    public function hasBeenReached(): bool
    {
        return false;
    }

    public function __toString(): string
    {
        return 'no time limit';
    }
}
