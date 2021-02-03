<?php

declare(strict_types=1);

namespace Eris\Contracts;

interface Progression
{
    public function next(int $value): int;
}
