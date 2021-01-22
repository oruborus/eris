<?php

declare(strict_types=1);

namespace Eris\Listener;

use Eris\Contracts\Listener;
use Eris\Contracts\TerminationCondition;
use Eris\Listener\EmptyListener;
use DateTime;
use DateInterval;

class TimeBasedTerminationCondition extends EmptyListener implements TerminationCondition, Listener
{
    private ?DateTime $limitTime = null;

    /**
     * @var callable():int $time
     */
    private $time;

    private DateInterval $maximumInterval;

    /**
     * @param callable():int $time
     */
    public function __construct($time, DateInterval $maximumInterval)
    {
        $this->time = $time;
        $this->maximumInterval = $maximumInterval;
    }

    public function startPropertyVerification(): void
    {
        $this->limitTime = $this->currentDateTime()->add($this->maximumInterval);
    }

    public function shouldTerminate(): bool
    {
        return $this->currentDateTime() >= $this->limitTime;
    }

    private function currentDateTime(): DateTime
    {
        return new DateTime('@' . (string) ($this->time)());
    }
}
