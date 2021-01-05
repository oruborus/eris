<?php

declare(strict_types=1);

namespace Test\E2E;

use OutOfBoundsException;
use Test\Examples\MinimumEvaluationsTest;
use Test\Support\EndToEndTestCase;

class EvaluationsTest extends EndToEndTestCase
{
    /**
     * @test
     */
    public function minimumEvaluations(): void
    {
        $this->runTestClass(MinimumEvaluationsTest::class)
            ->assertHadErrors(1)
            ->assertExceptionOnTest(OutOfBoundsException::class, 'failsBecauseOfTheLowEvaluationRatio')
            ->assertExceptionMessageOnTestMatches(
                '/Evaluation ratio 0\..* is under the threshold 0\.5/',
                'failsBecauseOfTheLowEvaluationRatio'
            );
    }
}
