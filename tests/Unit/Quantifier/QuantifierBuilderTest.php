<?php

declare(strict_types=1);

namespace Test\Unit\Quantifier;

use DateInterval;
use Eris\Contracts\Growth;
use Eris\Contracts\Listener;
use Eris\Contracts\Quantifier;
use Eris\Contracts\Source;
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
     * @covers Eris\Quantifier\QuantifierBuilder::limitTo
     *
     * @uses Eris\Quantifier\QuantifierBuilder::stopOn
     * @uses Eris\Quantifier\QuantifierBuilder::withMaximumIterations
     *
     * @uses Eris\TerminationCondition\TimeBasedTerminationCondition
     */
    public function canAddLimitToQuantifier(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $limit      = new DateInterval('PT2S');

        $quantifier->expects($this->once())->method('stopOn')->willReturnSelf();
        $quantifier->expects($this->once())->method('withMaximumIterations')->willReturnSelf();

        $dut = new QuantifierBuilder();

        $dut->limitTo(150)->limitTo($limit)->build($quantifier);
    }

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
     * @covers Eris\Quantifier\QuantifierBuilder::withGrowth
     */
    public function canSetTheGrowthTypeOfQuantifier(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $growth     = $this->getMockForAbstractClass(Growth::class, [], '', false);

        $quantifier
            ->expects($this->once())
            ->method('withGrowth')
            ->with($growth::class)
            ->willReturnSelf();

        $dut = new QuantifierBuilder();

        $dut->withGrowth($growth::class)->build($quantifier);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\QuantifierBuilder::build
     * @covers Eris\Quantifier\QuantifierBuilder::withSeed
     */
    public function canSetTheSeedOfQuantifier(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);

        $quantifier
            ->expects($this->once())
            ->method('withSeed')
            ->with(15)
            ->willReturnSelf();

        $dut = new QuantifierBuilder();

        $dut->withSeed(15)->build($quantifier);
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

    /**
     * @test
     *
     * @covers Eris\Quantifier\QuantifierBuilder::build
     * @covers Eris\Quantifier\QuantifierBuilder::withRand
     */
    public function canSetTheRandSourceTypeOfQuantifier(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $source     = $this->getMockForAbstractClass(Source::class, [], '', false);

        $quantifier
            ->expects($this->once())
            ->method('withRand')
            ->with($source::class)
            ->willReturnSelf();

        $dut = new QuantifierBuilder();

        $dut->withRand($source::class)->build($quantifier);
    }


    /**
     * @test
     *
     * @covers Eris\Quantifier\QuantifierBuilder::build
     * @covers Eris\Quantifier\QuantifierBuilder::withShrinkingTimeLimit
     */
    public function canSetTheShrinkingTimeLimitOfQuantifierGenerators(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);

        $quantifier
            ->expects($this->once())
            ->method('withShrinkingTimeLimit')
            ->with(15)
            ->willReturnSelf();

        $dut = new QuantifierBuilder();

        $dut->withShrinkingTimeLimit(15)->build($quantifier);
    }
}
