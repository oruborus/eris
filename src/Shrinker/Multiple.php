<?php

namespace Eris\Shrinker;

use Eris\Generator;
use Eris\Generator\GeneratedValue;
use Eris\Generator\GeneratedValueSingle;
use Eris\Generator\GeneratedValueOptions;
use Eris\Generator\TupleGenerator;
use Eris\Quantifier\Evaluation;
use Eris\Shrinker;
use Throwable;

class Multiple implements Shrinker
{
    private TupleGenerator $generator;
    /**
     * @var callable $assertion
     */
    private $assertion;
    /**
     * @var callable[] $goodShrinkConditions
     */
    private array $goodShrinkConditions = [];
    /**
     * @var callable[] $onAttempt
     */
    private array $onAttempt = [];
    private TimeLimit $timeLimit;

    /**
     * @param Generator[] $generators
     * @param callable $assertion
     */
    public function __construct(array $generators, $assertion)
    {
        $this->generator = new TupleGenerator($generators);
        $this->assertion = $assertion;
        $this->timeLimit = new NoTimeLimit();
    }

    public function setTimeLimit(TimeLimit $timeLimit): self
    {
        $this->timeLimit = $timeLimit;
        return $this;
    }

    /**
     * @param callable $condition
     */
    public function addGoodShrinkCondition($condition): self
    {
        $this->goodShrinkConditions[] = $condition;
        return $this;
    }

    /**
     * @param callable $listener
     */
    public function onAttempt($listener): self
    {
        $this->onAttempt[] = $listener;
        return $this;
    }

    /**
     * Precondition: $values should fail $this->assertion
     */
    public function from(GeneratedValue $elements, Throwable $exception): void
    {
        /**
         * @var GeneratedValue[] $branches
         */
        $branches = [];

        $shrink = function (GeneratedValue $elements) use (&$elementsAfterShrink, &$branches): array {
            $branches = [];
            $elementsAfterShrink = $this->generator->shrink($elements);
            if ($elementsAfterShrink instanceof GeneratedValueOptions) {
                foreach ($elementsAfterShrink as $each) {
                    $branches[] = $each;
                }
            } else {
                $branches[] = $elementsAfterShrink;
            }
            return $branches;
        };

        $onGoodShrink = function (GeneratedValue $elementsAfterShrink, Throwable $exceptionAfterShrink) use (&$elements, &$exception, &$branches, $shrink): void {
            $elements = $elementsAfterShrink;
            $exception = $exceptionAfterShrink;
            $branches = $shrink($elements);
        };

        $this->timeLimit->start();
        $shrink($elements);
        while ($elementsAfterShrink = array_shift($branches)) {
            if ($this->timeLimit->hasBeenReached()) {
                throw new \RuntimeException(
                    "Eris has reached the time limit for shrinking ($this->timeLimit), here it is presenting the simplest failure case." . PHP_EOL
                        . "If you can afford to spend more time to find a simpler failing input, increase it with the annotation \'@eris-shrink {seconds}\'.",
                    -1,
                    $exception
                );
            }
            // TODO: maybe not necessary
            // when Generator start returning emtpy options instead of the
            // element itself upon no shrinking
            // For now leave in for BC
            if ($elementsAfterShrink == $elements) {
                continue;
            }

            if (!$this->checkGoodShrinkConditions($elementsAfterShrink)) {
                continue;
            }

            foreach ($this->onAttempt as $onAttempt) {
                $onAttempt($elementsAfterShrink);
            }

            Evaluation::of($this->assertion)
                ->with($elementsAfterShrink)
                ->onFailure($onGoodShrink)
                ->execute();
        }

        throw $exception;
    }

    private function checkGoodShrinkConditions(GeneratedValue $values): bool
    {
        foreach ($this->goodShrinkConditions as $condition) {
            if (!$condition($values)) {
                return false;
            }
        }
        return true;
    }
}
