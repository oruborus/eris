<?php

namespace Eris\Quantifier;

use Eris\Value\Value;
use PHPUnit\Framework\AssertionFailedError;

/**
 * TODO: change namespace. To what?
 */
final class Evaluation
{
    /**
     * @var callable $assertion
     */
    private $assertion;
    /**
     * @var callable $onFailure
     */
    private $onFailure;
    /**
     * @var callable $onSuccess
     */
    private $onSuccess;
    private Value $values;

    /**
     * @param callable $assertion
     */
    public static function of($assertion): self
    {
        return new self($assertion);
    }

    /**
     * @param callable $assertion
     */
    private function __construct($assertion)
    {
        $this->assertion = $assertion;
        $this->onFailure = function (): void {
        };
        $this->onSuccess = function (): void {
        };
        // $this->values = new Value();
    }

    public function with(Value $values): self
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @param callable $action
     */
    public function onFailure($action): self
    {
        $this->onFailure = $action;
        return $this;
    }

    /**
     * @param callable $action
     */
    public function onSuccess($action): self
    {
        $this->onSuccess = $action;
        return $this;
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            call_user_func_array(
                $this->assertion,
                $this->values->unbox()
            );
        } catch (AssertionFailedError $e) {
            call_user_func($this->onFailure, $this->values, $e);
            return;
        }
        call_user_func($this->onSuccess, $this->values);
    }
}
