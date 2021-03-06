<?php

declare(strict_types=1);

namespace Test\Support\TerminationCondition;

use Eris\Contracts\TerminationCondition;

class TerminationSwitch implements TerminationCondition
{
    public function startPropertyVerification(): void
    {
    }

    private bool $shouldTerminate = false;

    public function abort(): void
    {
        $this->shouldTerminate = true;
    }

    public function shouldTerminate(): bool
    {
        return $this->shouldTerminate;
    }
}
