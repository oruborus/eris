<?php

declare(strict_types=1);

namespace Test\E2E;

use Test\Examples\LimitToTest;
use Test\Support\EndToEndTestCase;

/**
 * @coversNothing
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LimitTest extends EndToEndTestCase
{
    /**
     * @test
     */
    public function limitToTest(): void
    {
        $this->runTestClass(LimitToTest::class)
            ->assertWasSuccessful()
            ->assertAssertionCountOnTestWasEqual(
                5,
                'numberOfIterationsCanBeConfigured',
                'We configured a small number of iterations for this test, but a different number were performed'
            )
            ->assertAssertionCountOnTestWasLessThan(
                100,
                'timeIntervalToRunForCanBeConfiguredAndAVeryLowNumberOfIterationsCanBeIgnored',
                'We configured a small time limit for this test, but still all iterations were performed'
            )
            ->assertAssertionCountOnTestWasLessThan(
                100,
                'timeIntervalToRunForCanBeConfiguredAndAVeryLowNumberOfIterationsCanBeIgnoredFromAnnotation',
                'We configured a small time limit for this test, but still all iterations were performed'
            );
    }
}
