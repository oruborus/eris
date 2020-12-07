<?php

namespace Eris\Listener;

use Eris\Generator\GeneratedValueSingle;
use LogicException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class MinimumEvaluationsTest extends TestCase
{
    protected function setUp(): void
    {
        $this->listener = MinimumEvaluations::ratio(0.5);
    }

    public function testAllowsExecutionsWithHigherThanMinimumRatioToBeGreen()
    {
        $this->assertNull($this->listener->endPropertyVerification(99, 100));
    }

    public function testWarnsOfDangerouslyLowEvaluationRatiosAsVeryFewTestsAreBeingPerformed()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Evaluation ratio 0.2 is under the threshold 0.5');

        $this->listener->endPropertyVerification(20, 100);
    }

    public function testIfTheTestIsAlreadyFailingDoesNotCreateNoiseWithItsOwnCheck()
    {
        $this->assertNull(
            $this->listener->endPropertyVerification(10, 100, new LogicException("One of the cross beams has gone out askew on the treadle"))
        );
    }
}
