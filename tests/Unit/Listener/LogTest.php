<?php

declare(strict_types=1);

namespace Test\Unit\Listener;

use Eris\Listener\Log;
use InvalidArgumentException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

use function date_default_timezone_get;
use function date_default_timezone_set;
use function file_exists;
use function microtime;
use function sys_get_temp_dir;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LogTest extends TestCase
{
    private string $originalTimezone;

    private string $filename;

    /**
     * @var callable(): int $time
     */
    private $time;

    protected function setUp(): void
    {
        $this->originalTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');

        $this->filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'eris-log-unit-test.log';
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }

        $this->time = static fn (): int => 1300000000;
    }

    public function tearDown(): void
    {
        date_default_timezone_set($this->originalTimezone);

        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    /**
     * @test
     *
     * @covers Eris\Listener\Log::__construct
     * @covers Eris\Listener\Log::__destruct
     */
    public function suppliedFileGetsCreatedAndRemainsAfterLogging(): void
    {
        $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'eris-log-unit-test.log';
        if (file_exists($filename)) {
            unlink($filename);
        }

        $dut = new Log($filename, $this->time, 1234);

        unset($dut);

        $this->assertFileExists($filename);
        $this->assertStringEqualsFile($this->filename, '');
    }

    /**
     * @test
     *
     * @covers Eris\Listener\Log::__construct
     *
     * @uses Eris\Listener\Log::__destruct
     */
    public function throwsExceptionIfFileCanNotBeOpened(): void
    {
        $this->expectException(InvalidArgumentException::class);

        do {
            $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . microtime(false) . '.eris-log-unit-test.log';
        } while (file_exists($filename));

        $fp = fopen($filename, 'w');
        unlink($filename);

        try {
            new Log($filename, $this->time, 1234);
        } finally {
            fclose($fp);
        }
    }

    /**
     * @test
     *
     * @covers Eris\Listener\Log::__construct
     *
     * @uses Eris\Listener\Log::__destruct
     */
    public function throwsExceptionIfSuppliedFileNameIsDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $filename = sys_get_temp_dir();

        new Log($filename, $this->time, 1234);
    }

    /**
     * @test
     *
     * @covers Eris\Listener\Log::log
     * @covers Eris\Listener\Log::newGeneration
     *
     * @uses Eris\Listener\Log::__construct
     * @uses Eris\Listener\Log::__destruct
     *
     * @psalm-suppress InternalClass
     */
    public function writesALineForEachIterationShowingItsIndex(): void
    {
        $dut = new Log($this->filename, $this->time, 1234);

        $dut->newGeneration([23], 42);

        $this->assertStringEqualsFile(
            $this->filename,
            "[2011-03-13T07:06:40+00:00][1234] iteration 42: [23]" . PHP_EOL
        );
    }

    /**
     * @test
     *
     * @covers Eris\Listener\Log::failure
     * @covers Eris\Listener\Log::log
     *
     * @uses Eris\Listener\Log::__construct
     * @uses Eris\Listener\Log::__destruct
     *
     * @psalm-suppress InternalClass
     */
    public function writesALineForTheFirstFailureOfATest(): void
    {
        $dut = new Log($this->filename, $this->time, 1234);

        $dut->failure([23], new AssertionFailedError("Failed asserting that..."));

        $this->assertStringEqualsFile(
            $this->filename,
            "[2011-03-13T07:06:40+00:00][1234] failure: [23]. Failed asserting that..." . PHP_EOL
        );
    }

    /**
     * @test
     *
     * @covers Eris\Listener\Log::shrinking
     * @covers Eris\Listener\Log::log
     *
     * @uses Eris\Listener\Log::__construct
     * @uses Eris\Listener\Log::__destruct
     *
     * @psalm-suppress InternalClass
     */
    public function writesALineForEachShrinkingAttempt(): void
    {
        $dut = new Log($this->filename, $this->time, 1234);

        $dut->shrinking([22], new AssertionFailedError("Failed asserting that..."));


        $this->assertStringEqualsFile(
            $this->filename,
            "[2011-03-13T07:06:40+00:00][1234] shrinking: [22]" . PHP_EOL
        );
    }
}
