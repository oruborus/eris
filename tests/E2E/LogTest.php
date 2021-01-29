<?php

declare(strict_types=1);

namespace Test\E2E;

use PHPUnit\Framework\ExpectationFailedException;
use Test\Examples\LogFileTest;
use Test\Support\EndToEndTestCase;

/**
 * @coversNothing
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LogTest extends EndToEndTestCase
{
    /**
     * @test
     */
    public function logFileTests(): void
    {
        $this->runTestClass(LogFileTest::class)
            ->assertHadFailures(1)
            ->assertExceptionOnTest(ExpectationFailedException::class, 'logOfFailuresAndShrinking')
            ->assertExceptionMessageOnTest(
                'Failed asserting that 43 is equal to 42 or is less than 42.',
                'logOfFailuresAndShrinking'
            );

        // TODO: Evaluate sys_get_temp_dir() . '/eris-log-file-test.log'
        // TODO: Evaluate sys_get_temp_dir() . '/eris-log-file-shrinking.log'
    }
}
