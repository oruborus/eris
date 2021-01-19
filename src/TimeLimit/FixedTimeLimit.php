<?php

declare(strict_types=1);

namespace Eris\TimeLimit;

use Eris\Contracts\TimeLimit;

class FixedTimeLimit implements TimeLimit
{
    private int $maximumIntervalLength;

    /**
     * @var callable():int $clock
     */
    private $clock;

    private int $startOfTheInterval = -1;

    public static function realTime(int $maximumIntervalLength): self
    {
        return new self($maximumIntervalLength, '\time');
    }

    /**
     * @param int $maximumIntervalLength  in seconds
     * @param callable():int $clock
     */
    public function __construct(int $maximumIntervalLength, $clock)
    {
        $this->maximumIntervalLength = $maximumIntervalLength;
        $this->clock = $clock;
    }

    public function start(): void
    {
        $this->startOfTheInterval = ($this->clock)();
    }

    public function hasBeenReached(): bool
    {
        $actualIntervalLength = ($this->clock)() - $this->startOfTheInterval;
        return $actualIntervalLength >= $this->maximumIntervalLength;
    }

    public function __toString(): string
    {
        if ($this->startOfTheInterval === -1) {
            return 'TimeLimit has not been started.';
        }

        $actualIntervalLength = ($this->clock)() - $this->startOfTheInterval;
        return "{$actualIntervalLength}s elapsed of {$this->maximumIntervalLength}s";
    }
}
