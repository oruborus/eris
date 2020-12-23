<?php

declare(strict_types=1);

namespace Test\Unit\TimeLimit;

use Eris\TimeLimit\NoTimeLimit;
use PHPUnit\Framework\TestCase;

class NoTimeLimitTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\TimeLimit\NoTimeLimit
     */
    public function returnsAlwaysTheSame(): void
    {
        $dut = new NoTimeLimit();

        $this->assertSame('no time limit', (string) $dut);
        $this->assertFalse($dut->hasBeenReached());

        $dut->start();

        $this->assertSame('no time limit', (string) $dut);
        $this->assertFalse($dut->hasBeenReached());
    }
}
