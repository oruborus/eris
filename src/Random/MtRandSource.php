<?php

namespace Eris\Random;

use Eris\Contracts\Source;

class MtRandSource implements Source
{
    /**
     * Returns a random number between 0 and @see max().
     */
    public function extractNumber(): int
    {
        return mt_rand(0, $this->max());
    }

    public function max(): int
    {
        return mt_getrandmax();
    }

    public function seed(int $seed): self
    {
        mt_srand($seed);
        return $this;
    }
}
