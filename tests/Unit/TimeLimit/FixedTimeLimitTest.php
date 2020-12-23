<?php

declare(strict_types=1);

namespace Test\Unit\TimeLimit;

use Eris\TimeLimit\FixedTimeLimit;
use PHPUnit\Framework\TestCase;

class FixedTimeLimitTest extends TestCase
{
    private int $time = 0;

    /**
     * @test
     *
     * @covers Eris\TimeLimit\FixedTimeLimit::__construct
     * @covers Eris\TimeLimit\FixedTimeLimit::hasBeenReached
     * @covers Eris\TimeLimit\FixedTimeLimit::start
     */
    public function detectsAMaximumTimeHasElapsed(): void
    {
        $dut = new FixedTimeLimit(
            30,
            function (): int {
                return $this->time;
            }
        );
        $dut->start();

        $this->time = 0;
        $this->assertFalse($dut->hasBeenReached(), "Limit should not be immediately reached");

        $this->time = 29;
        $this->assertFalse($dut->hasBeenReached(), "Limit reached too soon");

        $this->time = 30;
        $this->assertTrue($dut->hasBeenReached(), "Limit not reached yet");
    }

    /**
     * @test
     *
     * @covers Eris\TimeLimit\FixedTimeLimit::__toString
     *
     * @uses Eris\TimeLimit\FixedTimeLimit::__construct
     * @uses Eris\TimeLimit\FixedTimeLimit::start
     */
    public function returnsElapsedTimeSinceStart(): void
    {
        $dut = new FixedTimeLimit(
            30,
            function (): int {
                return $this->time;
            }
        );

        $this->assertSame('TimeLimit has not been started.', (string) $dut);

        $dut->start();

        $this->time = 0;
        $this->assertSame("0s elapsed of 30s", (string) $dut);

        $this->time = 29;
        $this->assertSame("29s elapsed of 30s", (string) $dut);

        $this->time = 30;
        $this->assertSame("30s elapsed of 30s", (string) $dut);
    }

    /**
     * @test
     *
     * @covers Eris\TimeLimit\FixedTimeLimit::realTime
     *
     * @uses Eris\TimeLimit\FixedTimeLimit::__construct
     */
    public function staticCreateMethodInitiatesInstanceWithBuiltinTimeFunction(): void
    {
        $expected = new FixedTimeLimit(30, '\time');
        $dut = FixedTimeLimit::realTime(30);

        $this->assertEquals($expected, $dut);
    }
}
