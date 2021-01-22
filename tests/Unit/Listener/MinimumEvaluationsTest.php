<?php

declare(strict_types=1);

namespace Test\Unit\Listener;

use Eris\Listener\MinimumEvaluations;
use LogicException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class MinimumEvaluationsTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Listener\MinimumEvaluations::__construct
     * @covers Eris\Listener\MinimumEvaluations::endPropertyVerification
     * @covers Eris\Listener\MinimumEvaluations::ratio
     */
    public function allowsExecutionsWithEqualOrHigherThanMinimumRatioToBeGreen(): void
    {
        $dut = MinimumEvaluations::ratio(0.5);

        $this->assertNull($dut->endPropertyVerification(50, 100));
    }

    /**
     * @test
     *
     * @covers Eris\Listener\MinimumEvaluations::__construct
     * @covers Eris\Listener\MinimumEvaluations::endPropertyVerification
     * @covers Eris\Listener\MinimumEvaluations::ratio
     */
    public function warnsOfDangerouslyLowEvaluationRatiosAsVeryFewTestsAreBeingPerformed(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Evaluation ratio 0.2 is under the threshold 0.5');

        $dut = MinimumEvaluations::ratio(0.5);

        $dut->endPropertyVerification(20, 100);
    }

    /**
     * @test
     *
     * @covers Eris\Listener\MinimumEvaluations::__construct
     * @covers Eris\Listener\MinimumEvaluations::endPropertyVerification
     * @covers Eris\Listener\MinimumEvaluations::ratio
     */
    public function ifTheTestIsAlreadyFailingDoesNotCreateNoiseWithItsOwnCheck(): void
    {
        $dut = MinimumEvaluations::ratio(0.5);

        $this->assertNull(
            $dut->endPropertyVerification(
                10,
                100,
                new LogicException("One of the cross beams has gone out askew on the treadle")
            )
        );
    }
}
