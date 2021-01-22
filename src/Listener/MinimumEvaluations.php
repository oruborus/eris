<?php

declare(strict_types=1);

namespace Eris\Listener;

use Eris\Contracts\Listener;
use Eris\Listener\EmptyListener;
use OutOfBoundsException;
use Exception;

class MinimumEvaluations extends EmptyListener implements Listener
{
    private float $threshold;

    /**
     * @param float $threshold  from 0.0 to 1.0
     */
    public static function ratio(float $threshold): self
    {
        return new self($threshold);
    }

    private function __construct(float $threshold)
    {
        $this->threshold = $threshold;
    }

    public function endPropertyVerification(
        int $ordinaryEvaluations,
        int $iterations,
        ?Exception $exception = null
    ): void {
        if ($exception instanceof Exception) {
            return;
        }

        $evaluationRatio = $ordinaryEvaluations / $iterations;

        if ($evaluationRatio >= $this->threshold) {
            return;
        }

        throw new OutOfBoundsException("Evaluation ratio {$evaluationRatio} is under the threshold {$this->threshold}");
    }
}
