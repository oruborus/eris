<?php

declare(strict_types=1);

namespace Eris\Contracts;

interface TerminationCondition
{
    public function shouldTerminate(): bool;
}
