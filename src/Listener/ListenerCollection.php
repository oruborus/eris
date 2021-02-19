<?php

declare(strict_types=1);

namespace Eris\Listener;

use Eris\Contracts\Collection;
use Eris\Contracts\Listener;
use Exception;

/**
 * @extends Collection<Listener>
 */
class ListenerCollection extends Collection implements Listener
{
    /**
     * @param class-string<Listener> $listener
     */
    public function removeListenerOfType(string $listener): self
    {
        $newListeners = [];
        foreach ($this->elements as $listenerToCheck) {
            if ($listenerToCheck instanceof $listener) {
                continue;
            }

            $newListeners[] = $listenerToCheck;
        }

        $this->elements = $newListeners;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function startPropertyVerification(): void
    {
        foreach ($this->elements as $listener) {
            $listener->startPropertyVerification();
        }
    }

    /**
     * @inheritdoc
     */
    public function endPropertyVerification(int $ordinaryEvaluations, int $iterations, ?Exception $exception = null): void
    {
        foreach ($this->elements as $listener) {
            $listener->endPropertyVerification($ordinaryEvaluations, $iterations, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function newGeneration(array $generation, int $iteration): void
    {
        foreach ($this->elements as $listener) {
            $listener->newGeneration($generation, $iteration);
        }
    }

    /**
     * @inheritdoc
     */
    public function failure(array $generation, Exception $exception): void
    {
        foreach ($this->elements as $listener) {
            $listener->failure($generation, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function shrinking(array $generation): void
    {
        foreach ($this->elements as $listener) {
            $listener->shrinking($generation);
        }
    }
}
