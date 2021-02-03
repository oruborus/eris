<?php

declare(strict_types=1);

namespace Eris\Progression;

use function max;
use function min;

/**
 * Moves a value toward a limit such that the difference between two
 * members of the progression is constant.
 *
 * TODO: GeometricProgression where the ratio between two members
 *       of the progression is constant
 */
class ArithmeticProgression
{
    private int $limit;

    private int $step;

    public function __construct(int $limit, int $step = 1)
    {
        $this->limit = $limit;
        $this->step  = $step;
    }

    public function next(int $currentValue): int
    {
        $candidate = $currentValue + ($this->limit <=> $currentValue) * $this->step;

        if ($this->limit > $currentValue) {
            return max($currentValue, min($this->limit, $candidate));
        }

        return min($currentValue, max($this->limit, $candidate));
    }
}
