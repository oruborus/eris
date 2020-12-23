<?php

namespace Eris\TimeLimit;

use Eris\Contracts\TimeLimit;

class FixedTimeLimit implements TimeLimit
{
    private int $maximumIntervalLength;
    /**
     * @var callable $clock
     */
    private $clock;
    private int $startOfTheInterval = 0;

    public static function realTime(int $maximumIntervalLength): self
    {
        return new self($maximumIntervalLength, 'time');
    }

    /**
     * @param int $maximumIntervalLength  in seconds
     * @param callable $clock
     */
    public function __construct(int $maximumIntervalLength, $clock)
    {
        $this->maximumIntervalLength = $maximumIntervalLength;
        $this->clock = $clock;
    }

    public function start()
    {
        $this->startOfTheInterval = (int) call_user_func($this->clock);
    }

    public function hasBeenReached()
    {
        $actualIntervalLength = (int) call_user_func($this->clock) - $this->startOfTheInterval;
        return $actualIntervalLength >= $this->maximumIntervalLength;
    }

    public function __toString()
    {
        $actualIntervalLength = (int) call_user_func($this->clock) - $this->startOfTheInterval;
        return "{$actualIntervalLength}s elapsed of {$this->maximumIntervalLength}s";
    }
}
