<?php

namespace Eris\Listener;

use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    private $originalTimezone;
    protected function setUp(): void
    {
        $this->originalTimezone = date_default_timezone_get();
        $this->file = sys_get_temp_dir() . '/eris-log-unit-test.log';
        $this->time = function () {
            return 1300000000;
        };
        $this->log = new Log($this->file, $this->time, 1234);
        date_default_timezone_set('UTC');
    }

    public function tearDown(): void
    {
        $this->log->endPropertyVerification(null, null);
        date_default_timezone_set($this->originalTimezone);
    }

    public function testWritesALineForEachIterationShowingItsIndex()
    {
        $this->log->newGeneration([23], 42);
        $this->assertEquals(
            "[2011-03-13T07:06:40+00:00][1234] iteration 42: [23]" . PHP_EOL,
            file_get_contents($this->file)
        );
    }

    public function testWritesALineForTheFirstFailureOfATest()
    {
        $this->log->failure([23], new \PHPUnit_Framework_AssertionFailedError("Failed asserting that..."));
        $this->assertEquals(
            "[2011-03-13T07:06:40+00:00][1234] failure: [23]. Failed asserting that..." . PHP_EOL,
            file_get_contents($this->file)
        );
    }

    public function testWritesALineForEachShrinkingAttempt()
    {
        $this->log->shrinking([22], new \PHPUnit_Framework_AssertionFailedError("Failed asserting that..."));
        $this->assertEquals(
            "[2011-03-13T07:06:40+00:00][1234] shrinking: [22]" . PHP_EOL,
            file_get_contents($this->file)
        );
    }

    public function testCleansTheFile()
    {
        $this->log->newGeneration([], 42);
        $this->log = new Log($this->file, $this->time, 1234);

        $this->assertEquals("", file_get_contents($this->file));
    }
}
