<?php

declare(strict_types=1);

namespace Test\E2E;

use OutOfBoundsException;
use PHPUnit\Framework\ExpectationFailedException;
use Test\Examples\WhenTest;
use Test\Support\EndToEndTestCase;

class ConditionTest extends EndToEndTestCase
{
    /**
     * @test
     */
    public function whenTests(): void
    {
        $this->runTestClass(WhenTest::class)
            ->assertHadFailures(1)
            ->assertExceptionOnTest(
                ExpectationFailedException::class,
                'whenFailingWillNaturallyHaveALowEvaluationRatioSoWeDontWantThatErrorToObscureTheTrueOne'
            )
            ->assertExceptionMessageOnTestMatches(
                '/Failed asserting that \d+ is equal to 100 or is less than 100./',
                'whenFailingWillNaturallyHaveALowEvaluationRatioSoWeDontWantThatErrorToObscureTheTrueOne'
            )
            ->assertHadErrors(1)
            ->assertExceptionOnTest(
                OutOfBoundsException::class,
                'whenWhichSkipsTooManyValues'
            )
            ->assertExceptionMessageOnTestMatches(
                '/Evaluation ratio .* is under the threshold/',
                'whenWhichSkipsTooManyValues'
            );
    }
}
