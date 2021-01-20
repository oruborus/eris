<?php

namespace Eris\Random;

use Eris\Contracts\Source;

// TODO: Extract Interface
class RandomRange
{
    private Source $source;

    public function __construct(Source $source)
    {
        $this->source = $source;
    }

    public function seed(int $seed): void
    {
        $this->source->seed($seed);
    }

    /**
     * Return a random number.
     * If $lower and $upper are specified, the number will fall into their
     * inclusive range.
     * Otherwise the number from the source will be directly returned.
     */
    public function rand(?int $lower = null, ?int $upper = null): int
    {
        if ($lower === null || $upper === null) { //changed to logical or following the description
            return $this->source->extractNumber();
        }

        if ($lower > $upper) {
            list($lower, $upper) = [$upper, $lower];
        }
        $delta = $upper - $lower;
        $divisor = ($this->source->max()) / ($delta + 1);

        do {
            $retval = (int) floor($this->source->extractNumber() / $divisor);
        } while ($retval > $delta);

        return $retval + $lower;
    }
}
