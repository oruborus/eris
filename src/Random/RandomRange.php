<?php

declare(strict_types=1);

namespace Eris\Random;

use Eris\Contracts\Source;

/**
 * @todo Extract Interface
 */
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
     * If $lower and $upper are specified, the number will fall into their inclusive range.
     * Otherwise the number from the source will be directly returned.
     */
    public function rand(?int $lower = null, ?int $upper = null): int
    {
        if (
            is_null($lower) ||
            is_null($upper)
        ) {
            return $this->source->extractNumber();
        }

        if ($lower > $upper) {
            [$lower, $upper] = [$upper, $lower];
        }

        return $lower + (int) ($this->source->extractNumber() * ($upper - $lower) / $this->source->max());
    }
}
