<?php

namespace Eris\Listener;

use Eris\Listener;
use Eris\Listener\EmptyListener;
use OutOfBoundsException;
use Exception;

class MinimumEvaluations extends EmptyListener implements Listener
{
    private float $threshold;

    /**
     * @param float $threshold  from 0.0 to 1.0
     *
     * @return self
     */
    public static function ratio(float $threshold): self
    {
        return new self($threshold);
    }

    private function __construct(float $threshold)
    {
        $this->threshold = $threshold;
    }

    public function endPropertyVerification($ordinaryEvaluations, $iterations, Exception $exception = null)
    {
        if ($exception) {
            return;
        }
        $evaluationRatio = $ordinaryEvaluations / $iterations;
        if ($evaluationRatio < $this->threshold) {
            throw new OutOfBoundsException("Evaluation ratio {$evaluationRatio} is under the threshold {$this->threshold}");
        }
    }
}
