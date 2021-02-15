<?php

declare(strict_types=1);

namespace Eris\Contracts;

interface TerminationCondition
{
    public function startPropertyVerification(): void;

    public function shouldTerminate(): bool;
}
