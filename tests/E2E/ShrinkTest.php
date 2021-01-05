<?php

declare(strict_types=1);

namespace Test\E2E;

use PHPUnit\Framework\ExpectationFailedException;
use Test\Examples\DisableShrinkingTest;
use Test\Support\EndToEndTestCase;

use Test\Examples\ShrinkingTest;
use Test\Examples\ShrinkingTimeLimitTest;
use Test\Examples\StringTest;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ShrinkTest extends EndToEndTestCase
{
    /**
     * @test
     */
    public function stringShrinkingTest(): void
    {
        $this->runTestClass(StringTest::class)
            ->assertHadFailures(1)
            ->assertExceptionOnTest(ExpectationFailedException::class, 'lengthPreservation')
            ->assertExceptionMessageOnTestMatches(
                "/Concatenating '' to '.{6}' gives '.{6}ERROR'/",
                'lengthPreservation',
                "It seems there is a problem with shrinking: we were expecting a minimal error message " .
                    "but instead the one for StringTest::lengthPreservation() didn't match"
            );
    }

    /**
     * @test
     */
    public function shrinkingAndAntecedentsTests(): void
    {
        $this->runTestClass(ShrinkingTest::class)
            ->assertHadFailures(2)
            ->assertExceptionOnTest(ExpectationFailedException::class, 'shrinkingAString')
            ->assertExceptionMessageOnTestMatches(
                "/Failed asserting that .* does not contain 'B'/",
                'shrinkingAString'
            )
            ->assertExceptionOnTest(ExpectationFailedException::class, 'shrinkingRespectsAntecedents')
            ->assertExceptionMessageOnTestMatches(
                "/The number 11 is not multiple of 29/",
                'shrinkingRespectsAntecedents',
                "It seems there is a problem with shrinking: we were expecting an error message containing '11' since it's the lowest value in the domain that satisfies the antecedents."
            );
    }

    /**
     * @test
     */
    public function shrinkingTimeLimitTest(): void
    {
        $this->runTestsFromTestClass(ShrinkingTimeLimitTest::class, 'lengthPreservation')
            ->assertHadErrors(1)
            ->assertExceptionMessageOnTestMatches(
                '/Eris has reached the time limit for shrinking/',
                'lengthPreservation'
            )
            // one failure, two shrinking attempts: 2.0 + 2.0 == 4.0 seconds, plus some overhead
            ->assertExecutionTimeOnTestWasGreaterThanOrEqual(4.0, 'lengthPreservation')
            ->assertExecutionTimeOnTestWasLessThanOrEqual(5.0, 'lengthPreservation');
    }

    /**
     * @test
     */
    public function disableShrinkingTest(): void
    {
        $this->runTestClass(DisableShrinkingTest::class)
            ->assertHadFailures(1)
            ->assertExceptionOnTest(ExpectationFailedException::class, 'thenIsNotCalledMultipleTime')
            ->assertExceptionMessageOnTestMatches('/Total calls: 1\n/', 'thenIsNotCalledMultipleTime');
    }
}
