<?php

namespace Eris\Shrinker;

use Eris\Contracts\Generator;
use Eris\Contracts\Shrinker;
use Eris\Contracts\TimeLimit;
use Eris\Generator\TupleGenerator;
use Eris\Quantifier\Evaluation;
use Eris\TimeLimit\NoTimeLimit;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
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
    public function from(Value $elements, Throwable $exception): void
    {
        $branches = new ValueCollection();

        $shrink = function (Value $elements) use (&$elementsAfterShrink, &$branches): ValueCollection {
            $branches = new ValueCollection();
            $elementsAfterShrink = $this->generator->shrink($elements);
            foreach ($elementsAfterShrink as $each) {
                $branches[] = $each;
            }
            return $branches;
        };

        $onGoodShrink = function (Value $elementsAfterShrink, Throwable $exceptionAfterShrink) use (&$elements, &$exception, &$branches, $shrink): void {
            $elements = $elementsAfterShrink;
            $exception = $exceptionAfterShrink;
            $branches = $shrink($elements);
        };

        $this->timeLimit->start();
        $shrink($elements);
        while (true) {

            if (count($branches) === 0) {
                break;
            }

            $elementsAfterShrink = $branches->first();
            $branches->remove($elementsAfterShrink);

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

    private function checkGoodShrinkConditions(Value $values): bool
    {
        foreach ($this->goodShrinkConditions as $condition) {
            if (!$condition($values)) {
                return false;
            }
        }
        return true;
    }
}
