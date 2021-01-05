<?php

declare(strict_types=1);

namespace Test\Support;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestResult as PHPUnitTestResult;
use SimpleXMLElement;
use Test\Support\Constraints\AssertionCountOnTestWasEqual;
use Test\Support\Constraints\AssertionCountOnTestWasGreaterThan;
use Test\Support\Constraints\AssertionCountOnTestWasGreaterThanOrEqual;
use Test\Support\Constraints\AssertionCountOnTestWasLessThan;
use Test\Support\Constraints\AssertionCountOnTestWasLessThanOrEqual;
use Test\Support\Constraints\ExceptionCodeOnTest;
use Test\Support\Constraints\ExceptionMessageOnTest;
use Test\Support\Constraints\ExceptionMessageOnTestMatches;
use Test\Support\Constraints\ExceptionOnTest;
use Test\Support\Constraints\ExecutionTimeOnTestWasEqual;
use Test\Support\Constraints\ExecutionTimeOnTestWasGreaterThan;
use Test\Support\Constraints\ExecutionTimeOnTestWasGreaterThanOrEqual;
use Test\Support\Constraints\ExecutionTimeOnTestWasLessThan;
use Test\Support\Constraints\ExecutionTimeOnTestWasLessThanOrEqual;
use Test\Support\Constraints\HadErrors;
use Test\Support\Constraints\HadFailures;
use Test\Support\Constraints\HadNoErrors;
use Test\Support\Constraints\HadNoFailures;
use Test\Support\Constraints\HadNoWarnings;
use Test\Support\Constraints\HadWarnings;
use Test\Support\Constraints\WasSuccessful;
use Test\Support\Constraints\WasSuccessfulIgnoringWarnings;
use Throwable;

final class TestResult
{
    private PHPUnitTestResult $result;

    private SimpleXMLElement $log;

    private string $output;

    private string $name;

    public function __construct(PHPUnitTestResult $result, SimpleXMLElement $log, string $output)
    {
        $this->result = $result;
        $this->log    = $log;
        $this->output = $output;

        /**
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedPropertyFetch
         */
        $this->name   = (string) $this->log->testsuite->attributes()->name;
    }

    public function getResult(): PHPUnitTestResult
    {
        return $this->result;
    }

    public function getLog(): SimpleXMLElement
    {
        return $this->log;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function assertHadErrors(int $errorCount, string $message = ''): self
    {
        Assert::assertThat($this, static::hadErrors($errorCount), $message);

        return $this;
    }

    public static function hadErrors(int $errorCount): HadErrors
    {
        return new HadErrors($errorCount);
    }

    public function assertHadNoErrors(string $message = ''): self
    {
        Assert::assertThat($this, static::hadNoErrors(), $message);

        return $this;
    }

    public static function hadNoErrors(): HadNoErrors
    {
        return new HadNoErrors();
    }

    public function assertHadFailures(int $failureCount, string $message = ''): self
    {
        Assert::assertThat($this, static::hadFailures($failureCount), $message);

        return $this;
    }

    public static function hadFailures(int $failureCount): HadFailures
    {
        return new HadFailures($failureCount);
    }

    public function assertHadNoFailures(string $message = ''): self
    {
        Assert::assertThat($this, static::hadNoFailures(), $message);

        return $this;
    }

    public static function hadNoFailures(): HadNoFailures
    {
        return new HadNoFailures();
    }

    public function assertHadWarnings(int $failureCount, string $message = ''): self
    {
        Assert::assertThat($this, static::hadWarnings($failureCount), $message);

        return $this;
    }

    public static function hadWarnings(int $failureCount): HadWarnings
    {
        return new HadWarnings($failureCount);
    }

    public function assertHadNoWarnings(string $message = ''): self
    {
        Assert::assertThat($this, static::hadNoWarnings(), $message);

        return $this;
    }

    public static function hadNoWarnings(): HadNoWarnings
    {
        return new HadNoWarnings();
    }

    public function assertWasSuccessful(string $message = ''): self
    {
        Assert::assertThat($this, static::wasSuccessful(), $message);

        return $this;
    }

    public static function wasSuccessful(): WasSuccessful
    {
        return new WasSuccessful();
    }

    public function assertWasSuccessfulIgnoringWarnings(string $message = ''): self
    {
        Assert::assertThat($this, static::wasSuccessfulIgnoringWarnings(), $message);

        return $this;
    }

    public static function wasSuccessfulIgnoringWarnings(): WasSuccessfulIgnoringWarnings
    {
        return new WasSuccessfulIgnoringWarnings();
    }

    /**
     * @param class-string<Throwable> $exception
     */
    public function assertExceptionOnTest(string $exception, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::exceptionOnTest($exception, $method), $message);

        return $this;
    }

    public static function exceptionOnTest(string $exception, string $method): ExceptionOnTest
    {
        return new ExceptionOnTest($exception, $method);
    }

    public function assertExceptionMessageOnTest(string $exceptionMessage, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::exceptionMessageOnTest($exceptionMessage, $method), $message);

        return $this;
    }

    public static function exceptionMessageOnTest(string $message, string $method): ExceptionMessageOnTest
    {
        return new ExceptionMessageOnTest($message, $method);
    }

    public function assertExceptionCodeOnTest(int $code, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::exceptionCodeOnTest($code, $method), $message);

        return $this;
    }

    public static function exceptionCodeOnTest(int $message, string $method): ExceptionCodeOnTest
    {
        return new ExceptionCodeOnTest($message, $method);
    }


    public function assertExceptionMessageOnTestMatches(string $pattern, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::exceptionMessageOnTestMatches($pattern, $method), $message);

        return $this;
    }

    public static function exceptionMessageOnTestMatches(string $pattern, string $method): ExceptionMessageOnTestMatches
    {
        return new ExceptionMessageOnTestMatches($pattern, $method);
    }

    public function assertExecutionTimeOnTestWasEqual(int|float $time, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::executionTimeOnTestWasEqual($time, $method), $message);

        return $this;
    }

    public static function executionTimeOnTestWasEqual(int|float $time, string $method): ExecutionTimeOnTestWasEqual
    {
        return new ExecutionTimeOnTestWasEqual($time, $method);
    }

    public function assertExecutionTimeOnTestWasLessThan(int|float $time, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::executionTimeOnTestWasLessThan($time, $method), $message);

        return $this;
    }

    public static function executionTimeOnTestWasLessThan(int|float $time, string $method): ExecutionTimeOnTestWasLessThan
    {
        return new ExecutionTimeOnTestWasLessThan($time, $method);
    }

    public function assertExecutionTimeOnTestWasLessThanOrEqual(int|float $time, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::executionTimeOnTestWasLessThanOrEqual($time, $method), $message);

        return $this;
    }

    public static function executionTimeOnTestWasLessThanOrEqual(int|float $time, string $method): ExecutionTimeOnTestWasLessThanOrEqual
    {
        return new ExecutionTimeOnTestWasLessThanOrEqual($time, $method);
    }

    public function assertExecutionTimeOnTestWasGreaterThan(int|float $time, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::executionTimeOnTestWasGreaterThan($time, $method), $message);

        return $this;
    }

    public static function executionTimeOnTestWasGreaterThan(int|float $time, string $method): ExecutionTimeOnTestWasGreaterThan
    {
        return new ExecutionTimeOnTestWasGreaterThan($time, $method);
    }

    public function assertExecutionTimeOnTestWasGreaterThanOrEqual(int|float $time, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::executionTimeOnTestWasGreaterThanOrEqual($time, $method), $message);

        return $this;
    }

    public static function executionTimeOnTestWasGreaterThanOrEqual(int|float $time, string $method): ExecutionTimeOnTestWasGreaterThanOrEqual
    {
        return new ExecutionTimeOnTestWasGreaterThanOrEqual($time, $method);
    }

    public function assertAssertionCountOnTestWasEqual(int $count, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::assertionCountOnTestWasEqual($count, $method), $message);

        return $this;
    }

    public static function assertionCountOnTestWasEqual(int $count, string $method): AssertionCountOnTestWasEqual
    {
        return new AssertionCountOnTestWasEqual($count, $method);
    }

    public function assertAssertionCountOnTestWasLessThan(int $count, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::assertionCountOnTestWasLessThan($count, $method), $message);

        return $this;
    }

    public static function assertionCountOnTestWasLessThan(int $count, string $method): AssertionCountOnTestWasLessThan
    {
        return new AssertionCountOnTestWasLessThan($count, $method);
    }

    public function assertAssertionCountOnTestWasLessThanOrEqual(int $count, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::assertionCountOnTestWasLessThanOrEqual($count, $method), $message);

        return $this;
    }

    public static function assertionCountOnTestWasLessThanOrEqual(int $count, string $method): AssertionCountOnTestWasLessThanOrEqual
    {
        return new AssertionCountOnTestWasLessThanOrEqual($count, $method);
    }

    public function assertAssertionCountOnTestWasGreaterThan(int $count, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::assertionCountOnTestWasGreaterThan($count, $method), $message);

        return $this;
    }

    public static function assertionCountOnTestWasGreaterThan(int $count, string $method): AssertionCountOnTestWasGreaterThan
    {
        return new AssertionCountOnTestWasGreaterThan($count, $method);
    }

    public function assertAssertionCountOnTestWasGreaterThanOrEqual(int $count, string $method, string $message = ''): self
    {
        Assert::assertThat($this, static::assertionCountOnTestWasGreaterThanOrEqual($count, $method), $message);

        return $this;
    }

    public static function assertionCountOnTestWasGreaterThanOrEqual(int $count, string $method): AssertionCountOnTestWasGreaterThanOrEqual
    {
        return new AssertionCountOnTestWasGreaterThanOrEqual($count, $method);
    }
}
