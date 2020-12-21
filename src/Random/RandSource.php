<?php

namespace Eris\Random;

use Eris\Contracts\Source;

class RandSource implements Source
{
    /**
     * Returns a random number between 0 and @see max().
     */
    public function extractNumber(): int
    {
        return rand(0, $this->max());
    }

    public function max(): int
    {
        return getrandmax();
    }

    public function seed(int $seed): self
    {
        srand($seed);
        return $this;
    }
}
