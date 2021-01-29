<?php

declare(strict_types=1);

namespace Test\E2E;

use Test\Examples\AlwaysFailsTest;
use Test\Examples\ErrorTest;
use Test\Support\EndToEndTestCase;

/**
 * @coversNothing
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class EnvironmentTest extends EndToEndTestCase
{
    /**
     * @test
     */
    public function genericErrorTest(): void
    {
        // TODO: turn on this by default? Or remove it?
        $this->setEnvironmentVariable('ERIS_ORIGINAL_INPUT', '1');

        $this->runTestClass(ErrorTest::class)
            ->assertHadErrors(1)
            ->assertExceptionMessageOnTestMatches(
                '/Original input:/',
                'genericExceptionsDoNotShrinkButStillShowTheInput'
            );
    }

    /**
     * @test
     */
    public function reproducibilityWithSeed(): void
    {
        $result1 = $this->runTestClass(AlwaysFailsTest::class);

        if (!preg_match('/ERIS_SEED=([0-9]+)/', $result1->getOutput(), $matches)) {
            $this->fail("Cannot find ERIS_SEED in output to rerun the test deterministically: {$result1->getOutput()}");
        }

        $this->setEnvironmentVariable('ERIS_SEED', $matches[1]);

        $result2 = $this->runTestClass(AlwaysFailsTest::class);

        [$failure1UntilStackTrace] = explode("\n\n", (string) $result1->getLog()->testsuite->testcase->failure);
        [$failure2UntilStackTrace] = explode("\n\n", (string) $result2->getLog()->testsuite->testcase->failure);

        $this->assertEquals($failure1UntilStackTrace, $failure2UntilStackTrace);
    }
}
