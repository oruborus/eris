<?php

namespace Eris\Random;

interface Source
{
    public function seed(int $seed): self;

    /**
     * Returns a random number between 0 and @see max().
     */
    public function extractNumber(): int;

    public function max(): int;
}
