<?php

namespace Eris\Quantifier;

use Eris\Listener;
use Eris\Listener\EmptyListener;
use DateTime;
use DateInterval;

class TimeBasedTerminationCondition extends EmptyListener implements TerminationCondition, Listener
{
    private ?DateTime $limitTime = null;
    /**
     * @var callable $time
     */
    private $time;
    private DateInterval $maximumInterval;

    /**
     * @param callable $time
     */
    public function __construct($time, DateInterval $maximumInterval)
    {
        $this->time = $time;
        $this->maximumInterval = $maximumInterval;
    }

    public function startPropertyVerification(): void
    {
        $this->limitTime = $this
            ->currentDateTime()
            ->add($this->maximumInterval);
    }

    public function shouldTerminate(): bool
    {
        return $this->currentDateTime() >= $this->limitTime;
    }

    private function currentDateTime(): DateTime
    {
        return new DateTime("@" . (string) call_user_func($this->time));
    }
}
