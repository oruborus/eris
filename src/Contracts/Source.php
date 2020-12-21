<?php

declare(strict_types=1);

namespace Eris\Contracts;

interface Source
{
    public function seed(int $seed): self;

    /**
     * Returns a random number between 0 and @see max().
     */
    public function extractNumber(): int;

    public function max(): int;
}
