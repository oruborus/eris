<?php

declare(strict_types=1);

namespace Test\Unit\TerminationCondition;

use Eris\Contracts\TerminationCondition;
use Eris\TerminationCondition\TerminationConditionCollection;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @uses Eris\Contracts\Collection
 */
class TerminationConditionCollectionTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\TerminationCondition\TerminationConditionCollection
     */
    public function terminationConditionsCanBeAddedAndTheirTerminationStatusCanBeRetrieved(): void
    {
        $terminationCondition1 = $this->getMockForAbstractClass(TerminationCondition::class);
        $terminationCondition1
            ->expects($this->once())
            ->method('startPropertyVerification');
        $terminationCondition1
            ->expects($this->exactly(4))
            ->method('shouldTerminate')
            ->willReturn(true, true, false, false);
        $terminationCondition2 = $this->getMockForAbstractClass(TerminationCondition::class);
        $terminationCondition2
            ->expects($this->once())
            ->method('startPropertyVerification');
        $terminationCondition2
            ->expects($this->exactly(2))
            ->method('shouldTerminate')
            ->willReturn(true, false, true, false);

        $dut = new TerminationConditionCollection($terminationCondition1, $terminationCondition2);

        $dut->startPropertyVerification();

        $this->assertTrue($dut->shouldTerminate());
        $this->assertTrue($dut->shouldTerminate());
        $this->assertTrue($dut->shouldTerminate());
        $this->assertFalse($dut->shouldTerminate());
    }
}
