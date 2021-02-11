<?php

declare(strict_types=1);

namespace Eris\Listener;

use Eris\Contracts\Listener;
use Exception;

class ListenerCollection implements Listener
{
    /**
     * @var Listener[] $listeners
     */
    private array $listeners = [];

    /**
     * @param Listener[] $listeners
     */
    public function __construct(array $listeners = [])
    {
        $this->listeners = $listeners;
    }

    public function add(Listener $listener): self
    {
        $this->listeners[] = $listener;

        return $this;
    }

    /**
     * @param class-string<Listener> $listener
     */
    public function removeListenerOfType(string $listener): self
    {
        $newListeners = [];
        foreach ($this->listeners as &$listenerToCheck) {
            if ($listenerToCheck instanceof $listener) {
                continue;
            }

            $newListeners[] = $listenerToCheck;
        }

        $this->listeners = $newListeners;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function startPropertyVerification(): void
    {
        foreach ($this->listeners as $listener) {
            $listener->startPropertyVerification();
        }
    }

    /**
     * @inheritdoc
     */
    public function endPropertyVerification(int $ordinaryEvaluations, int $iterations, ?Exception $exception = null): void
    {
        foreach ($this->listeners as $listener) {
            $listener->endPropertyVerification($ordinaryEvaluations, $iterations, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function newGeneration(array $generation, int $iteration): void
    {
        foreach ($this->listeners as $listener) {
            $listener->newGeneration($generation, $iteration);
        }
    }

    /**
     * @inheritdoc
     */
    public function failure(array $generation, Exception $exception): void
    {
        foreach ($this->listeners as $listener) {
            $listener->failure($generation, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function shrinking(array $generation): void
    {
        foreach ($this->listeners as $listener) {
            $listener->shrinking($generation);
        }
    }
}
