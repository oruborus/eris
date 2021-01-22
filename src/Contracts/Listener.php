<?php

declare(strict_types=1);

namespace Eris\Contracts;

use Exception;

interface Listener
{
    public function startPropertyVerification(): void;

    /**
     * @param int $ordinaryEvaluations  the number of inputs effectively evaluated, not filtered out.
     *                                  Does not count evaluations used in shrinking
     * @param int $iterations  the total number of inputs that have been generated
     * @param null|Exception $exception  tells if the test has failed and specifies the exact exception.
     */
    public function endPropertyVerification(
        int $ordinaryEvaluations,
        int $iterations,
        ?Exception $exception = null
    ): void;

    /**
     * @param array $generation  of mixed values
     * @param int $iteration  index of current iteration
     */
    public function newGeneration(array $generation, int $iteration): void;

    /**
     * @param array<mixed> $generation  of mixed values
     * @param Exception $exception  assertion failure
     */
    public function failure(array $generation, Exception $exception): void;

    /**
     * @param array<mixed> $generation  of mixed values
     */
    public function shrinking(array $generation): void;
}
