<?php

declare(strict_types=1);

namespace Eris\Shrinker;

use Eris\Contracts\Generator;
use Eris\Contracts\Shrinker;
use Eris\Contracts\TimeLimit;
use Eris\Generator\GeneratorCollection;
use Eris\TimeLimit\NoTimeLimit;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use PHPUnit\Framework\AssertionFailedError;
use RuntimeException;
use Throwable;

use function array_map;

class Multiple implements Shrinker
{
    private GeneratorCollection $generators;

    /**
     * @var callable(mixed...):void $assertion
     */
    private $assertion;

    /**
     * @var list<callable(Value):bool> $goodShrinkConditions
     */
    private array $goodShrinkConditions = [];

    /**
     * @var callable[] $onAttempt
     */
    private array $onAttempt = [];

    private TimeLimit $timeLimit;

    /**
     * @param callable(mixed...):void $assertion
     */
    public function __construct(GeneratorCollection $generators, $assertion)
    {
        $this->generators = $generators;
        $this->assertion = $assertion;
        $this->timeLimit = new NoTimeLimit();
    }

    public function setTimeLimit(TimeLimit $timeLimit): self
    {
        $this->timeLimit = $timeLimit;
        return $this;
    }

    public function getTimeLimit(): TimeLimit
    {
        return $this->timeLimit;
    }

    /**
     * @param callable(Value):bool $condition
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
    public function from(Value $currentElement, Throwable $exception): void
    {
        $this->timeLimit->start();

        $branches = $this->shrink($currentElement);

        while ($firstBranch = $branches->shift()) {
            if ($this->timeLimit->hasBeenReached()) {
                throw new RuntimeException(
                    "Eris has reached the time limit for shrinking ($this->timeLimit), here it is presenting the simplest failure case." . PHP_EOL
                        . "If you can afford to spend more time to find a simpler failing input, increase it with the annotation \'@eris-shrink {seconds}\'.",
                    -1,
                    $exception
                );
            }

            if ($firstBranch == $currentElement) {
                continue;
            }

            if (!$this->checkGoodShrinkConditions($firstBranch)) {
                continue;
            }

            foreach ($this->onAttempt as $onAttempt) {
                $onAttempt($firstBranch);
            }

            try {
                /**
                 * @psalm-suppress MixedArgument
                 */ ($this->assertion)(...$firstBranch->value());
            } catch (AssertionFailedError $e) {
                $currentElement = $firstBranch;
                $exception = $e;
                $branches = $this->shrink($currentElement);
            }
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

    private function shrink(Value $value): ValueCollection
    {
        /**
         * @psalm-suppress MixedArgument
         * @var list<array{0:Generator, 1:Value}> $generatorInputPair
         */
        $generatorInputPair = array_map(null, $this->generators->all(), $value->input());

        $result = new ValueCollection();
        foreach ($generatorInputPair as [$generator, $input]) {
            $shrunkValues = $generator->shrink($input);
            $shrunkValues[] = $input;

            $options = new ValueCollection();
            foreach ($shrunkValues as $value) {
                $options[] = new Value([$value->value()], [$value]);
            }

            /**
             * @psalm-suppress MixedArgumentTypeCoercion
             */
            $result = count($result) ? $result->cartesianProduct($options, '\array_merge') : $options;
        }

        return $result->remove($value);
    }
}
