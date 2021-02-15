<?php

declare(strict_types=1);

namespace Test\Unit\TerminationCondition;

use DateInterval;
use Eris\TerminationCondition\TimeBasedTerminationCondition;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TimeBasedTerminationConditionTest extends TestCase
{
    private int $currentTime;

    /**
     * @var callable():int $time
     */
    private $time;

    protected function setUp(): void
    {
        $this->currentTime = 1300000000;
        $this->time = fn (): int => $this->currentTime;
    }

    /**
     * @test
     *
     * @covers Eris\TerminationCondition\TimeBasedTerminationCondition::__construct
     * @covers Eris\TerminationCondition\TimeBasedTerminationCondition::currentDateTime
     * @covers Eris\TerminationCondition\TimeBasedTerminationCondition::startPropertyVerification
     * @covers Eris\TerminationCondition\TimeBasedTerminationCondition::shouldTerminate
     */
    public function defaultsToNotTerminateAtStartup(): void
    {
        $dut = new TimeBasedTerminationCondition($this->time, new DateInterval('PT1800S'));

        $dut->startPropertyVerification();
        $actual = $dut->shouldTerminate();

        $this->assertFalse($actual);
    }

    /**
     * @test
     *
     * @covers Eris\TerminationCondition\TimeBasedTerminationCondition::__construct
     * @covers Eris\TerminationCondition\TimeBasedTerminationCondition::currentDateTime
     * @covers Eris\TerminationCondition\TimeBasedTerminationCondition::startPropertyVerification
     * @covers Eris\TerminationCondition\TimeBasedTerminationCondition::shouldTerminate
     */
    public function whenAnIntervalShorterThanTheMaximumIntervalIsElapsedChoosesNotToTerminate(): void
    {
        $dut = new TimeBasedTerminationCondition($this->time, new DateInterval('PT1800S'));

        $dut->startPropertyVerification();
        $this->currentTime = 1300001000;
        $actual = $dut->shouldTerminate();

        $this->assertFalse($actual);
    }

    /**
     * @test
     *
     * @covers Eris\TerminationCondition\TimeBasedTerminationCondition::__construct
     * @covers Eris\TerminationCondition\TimeBasedTerminationCondition::currentDateTime
     * @covers Eris\TerminationCondition\TimeBasedTerminationCondition::startPropertyVerification
     * @covers Eris\TerminationCondition\TimeBasedTerminationCondition::shouldTerminate
     */
    public function whenTheMaximumIntervalIsElapsedChoosesToTerminate(): void
    {
        $dut = new TimeBasedTerminationCondition($this->time, new DateInterval('PT1800S'));

        $dut->startPropertyVerification();
        $this->currentTime = 1300002000;
        $actual = $dut->shouldTerminate();

        $this->assertTrue($actual);
    }
}
