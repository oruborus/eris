<?php

declare(strict_types=1);

namespace Test\Unit\TimeLimit;

use Eris\TimeLimit\FixedTimeLimit;
use PHPUnit\Framework\TestCase;

class FixedTimeLimitTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\TimeLimit\FixedTimeLimit::__construct
     * @covers Eris\TimeLimit\FixedTimeLimit::hasBeenReached
     * @covers Eris\TimeLimit\FixedTimeLimit::start
     */
    public function detectsAMaximumTimeHasElapsed(): void
    {
        $this->time = 1000000000;
        $limit = new FixedTimeLimit(
            30,
            function (): int {
                return $this->time;
            }
        );
        $limit->start();

        $this->assertFalse($limit->hasBeenReached(), "Limit should not be immediately reached");

        $this->time = 1000000029;
        $this->assertFalse($limit->hasBeenReached(), "Limit reached too soon");

        $this->time = 1000000030;
        $this->assertTrue($limit->hasBeenReached(), "Limit not reached yet");
    }
}
