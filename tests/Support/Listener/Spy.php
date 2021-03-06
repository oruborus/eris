<?php

declare(strict_types=1);

namespace Test\Support\Listener;

use Eris\Contracts\Listener;
use Exception;

class Spy implements Listener
{
    /**
     * @var null|callable():void $startPropertyVerificationAssertion
     */
    private $startPropertyVerificationAssertion = null;

    /**
     * @param null|callable():void $assertion
     */
    public function setStartPropertyVerificationAssertion($assertion): self
    {
        $this->startPropertyVerificationAssertion = $assertion;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function startPropertyVerification(): void
    {
        if (is_null($this->startPropertyVerificationAssertion)) {
            return;
        }

        ($this->startPropertyVerificationAssertion)();
    }

    /**
     * @var null|callable(int, int, null|Exception):void $endPropertyVerificationAssertion
     */
    private $endPropertyVerificationAssertion = null;

    /**
     * @param null|callable(int, int, null|Exception):void $assertion
     */
    public function setEndPropertyVerificationAssertion($assertion): self
    {
        $this->endPropertyVerificationAssertion = $assertion;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function endPropertyVerification(
        int $ordinaryEvaluations,
        int $iterations,
        ?Exception $exception = null
    ): void {
        if (is_null($this->endPropertyVerificationAssertion)) {
            return;
        }

        ($this->endPropertyVerificationAssertion)($ordinaryEvaluations, $iterations, $exception);
    }

    /**
     * @var null|callable(array, int):void $newGenerationAssertion
     */
    private $newGenerationAssertion = null;

    /**
     * @param null|callable(array, int):void $assertion
     */
    public function setNewGenerationAssertion($assertion): self
    {
        $this->newGenerationAssertion = $assertion;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function newGeneration(array $generation, int $iteration): void
    {
        if (is_null($this->newGenerationAssertion)) {
            return;
        }

        ($this->newGenerationAssertion)($generation, $iteration);
    }

    /**
     * @var null|callable(array, Exception):void $failureAssertion
     */
    private $failureAssertion = null;

    /**
     * @param null|callable(array, Exception):void $assertion
     */
    public function setFailureAssertion($assertion): self
    {
        $this->failureAssertion = $assertion;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function failure(array $generation, Exception $exception): void
    {
        if (is_null($this->failureAssertion)) {
            return;
        }

        ($this->failureAssertion)($generation, $exception);
    }

    /**
     * @var null|callable(array):void $shrinkingAssertion
     */
    private $shrinkingAssertion = null;

    /**
     * @param null|callable(array):void $assertion
     */
    public function setShrinkingAssertion($assertion): self
    {
        $this->shrinkingAssertion = $assertion;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function shrinking(array $generation): void
    {
        if (is_null($this->shrinkingAssertion)) {
            return;
        }

        ($this->shrinkingAssertion)($generation);
    }
}
