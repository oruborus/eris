<?php

declare(strict_types=1);

namespace Test\Unit\Listener;

use Eris\Listener\LogListener;
use PHPUnit\Framework\TestCase;

use function Eris\Listener\log;
use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

/**
 * @covers Eris\Listener\log
 *
 * @uses Eris\Listener\LogListener
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LogFunctionTest extends TestCase
{
    /**
     * @test
     */
    public function createsALogListener(): void
    {
        $dut = log(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'log-function-test.tmp');

        $this->assertInstanceOf(LogListener::class, $dut);
    }
}
