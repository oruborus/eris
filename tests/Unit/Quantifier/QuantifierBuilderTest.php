<?php

declare(strict_types=1);

namespace Test\Unit\Quantifier;

use Eris\Contracts\Listener;
use Eris\Contracts\Quantifier;
use Eris\Contracts\TerminationCondition;
use Eris\Quantifier\QuantifierBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class QuantifierBuilderTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Quantifier\QuantifierBuilder::build
     * @covers Eris\Quantifier\QuantifierBuilder::listenTo
     */
    public function canAddListenersToQuantifier(): void
    {
        $listener   = $this->getMockForAbstractClass(Listener::class);
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);

        $quantifier->expects($this->exactly(2))->method('listenTo')->willReturnSelf();

        $dut = new QuantifierBuilder();

        $dut->listenTo($listener)->listenTo($listener)->build($quantifier);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\QuantifierBuilder::build
     * @covers Eris\Quantifier\QuantifierBuilder::stopOn
     */
    public function canAddTerminationConditionsToQuantifier(): void
    {
        $terminationCondition = $this->getMockForAbstractClass(TerminationCondition::class);
        $quantifier           = $this->getMockForAbstractClass(Quantifier::class);

        $quantifier->expects($this->exactly(2))->method('stopOn')->willReturnSelf();

        $dut = new QuantifierBuilder();

        $dut->stopOn($terminationCondition)->stopOn($terminationCondition)->build($quantifier);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\QuantifierBuilder::build
     * @covers Eris\Quantifier\QuantifierBuilder::withMaximumIterations
     */
    public function canSetTheMaximumIterationCountOfQuantifier(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);

        $quantifier
            ->expects($this->once())
            ->method('withMaximumIterations')
            ->with(15)
            ->willReturnSelf();

        $dut = new QuantifierBuilder();

        $dut->withMaximumIterations(15)->build($quantifier);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\QuantifierBuilder::build
     * @covers Eris\Quantifier\QuantifierBuilder::withMaximumSize
     */
    public function canSetTheSizeOfQuantifierGenerators(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);

        $quantifier
            ->expects($this->once())
            ->method('withMaximumSize')
            ->with(15)
            ->willReturnSelf();

        $dut = new QuantifierBuilder();

        $dut->withMaximumSize(15)->build($quantifier);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\QuantifierBuilder::build
     * @covers Eris\Quantifier\QuantifierBuilder::withoutShrinking
     */
    public function canDisableShrinkingForQuantifier(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);

        $quantifier
            ->expects($this->once())
            ->method('withoutShrinking')
            ->willReturnSelf();

        $dut = new QuantifierBuilder();

        $dut->withoutShrinking()->build($quantifier);
    }
}
