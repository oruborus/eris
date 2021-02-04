<?php

declare(strict_types=1);

namespace Eris\Listener;

use Eris\Contracts\Listener;
use Exception;

/**
 * @codeCoverageIgnore
 */
abstract class EmptyListener implements Listener
{
    public function startPropertyVerification(): void
    {
    }

    public function endPropertyVerification(
        int $ordinaryEvaluations,
        int $iterations,
        ?Exception $exception = null
    ): void {
    }

    public function newGeneration(array $generation, int $iteration): void
    {
    }

    public function failure(array $generation, Exception $exception): void
    {
    }

    public function shrinking(array $generation): void
    {
    }
}
