<?php

declare(strict_types=1);

namespace Test\Unit\Quantifier;

use Eris\Contracts\Listener;
use Eris\Contracts\Quantifier;
use Eris\Contracts\TerminationCondition;
use Eris\Quantifier\QuantifierBuilder;
use Eris\Quantifier\CanConfigureQuantifier;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @uses Eris\Quantifier\QuantifierBuilder
 */
class CanConfigureQuantifierTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Quantifier\CanConfigureQuantifier::getQuantifierBuilder
     */
    public function holdSingletonForQuantifierBuilder(): void
    {
        $dut = new class()
        {
            use CanConfigureQuantifier;

            public function run(): QuantifierBuilder
            {
                return $this->getQuantifierBuilder();
            }
        };

        $first  = $dut->run();
        $second = $dut->run();

        $this->assertInstanceOf(QuantifierBuilder::class, $first);
        $this->assertSame($first, $second);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\CanConfigureQuantifier::listenTo
     *
     * @uses Eris\Quantifier\CanConfigureQuantifier::getQuantifierBuilder
     */
    public function canStageListenersForQuantifierCreation(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $quantifier
            ->expects($this->exactly(2))
            ->method('listenTo')
            ->willReturnSelf();

        $listener = $this->getMockForAbstractClass(Listener::class);

        $dut = new class()
        {
            use CanConfigureQuantifier {
                listenTo as public;
            }

            public function run(Quantifier $quantifier): void
            {
                $this->getQuantifierBuilder()->build($quantifier);
            }
        };

        $dut->listenTo($listener)->listenTo($listener)->run($quantifier);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\CanConfigureQuantifier::stopOn
     *
     * @uses Eris\Quantifier\CanConfigureQuantifier::getQuantifierBuilder
     */
    public function canStageTerminationConditionsForQuantifierCreation(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $quantifier
            ->expects($this->exactly(2))
            ->method('stopOn')
            ->willReturnSelf();

        $terminationCondition = $this->getMockForAbstractClass(TerminationCondition::class);

        $dut = new class()
        {
            use CanConfigureQuantifier {
                stopOn as public;
            }

            public function run(Quantifier $quantifier): void
            {
                $this->getQuantifierBuilder()->build($quantifier);
            }
        };

        $dut->stopOn($terminationCondition)->stopOn($terminationCondition)->run($quantifier);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\CanConfigureQuantifier::withMaximumIterations
     *
     * @uses Eris\Quantifier\CanConfigureQuantifier::getQuantifierBuilder
     */
    public function canStageMaximumIterationCountForQuantifierCreation(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $quantifier
            ->expects($this->once())
            ->method('withMaximumIterations')
            ->with(15)
            ->willReturnSelf();

        $dut = new class()
        {
            use CanConfigureQuantifier {
                withMaximumIterations as public;
            }

            public function run(Quantifier $quantifier): void
            {
                $this->getQuantifierBuilder()->build($quantifier);
            }
        };

        $dut->withMaximumIterations(15)->run($quantifier);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\CanConfigureQuantifier::withMaximumSize
     *
     * @uses Eris\Quantifier\CanConfigureQuantifier::getQuantifierBuilder
     */
    public function canStageMaximumSizeForQuantifierCreation(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $quantifier
            ->expects($this->once())
            ->method('withMaximumSize')
            ->with(15)
            ->willReturnSelf();

        $dut = new class()
        {
            use CanConfigureQuantifier {
                withMaximumSize as public;
            }

            public function run(Quantifier $quantifier): void
            {
                $this->getQuantifierBuilder()->build($quantifier);
            }
        };

        $dut->withMaximumSize(15)->run($quantifier);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\CanConfigureQuantifier::withoutShrinking
     *
     * @uses Eris\Quantifier\CanConfigureQuantifier::getQuantifierBuilder
     */
    public function canStageShrinkingFlagForQuantifierCreation(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $quantifier
            ->expects($this->once())
            ->method('withoutShrinking')
            ->willReturnSelf();

        $dut = new class()
        {
            use CanConfigureQuantifier {
                withoutShrinking as public;
            }

            public function run(Quantifier $quantifier): void
            {
                $this->getQuantifierBuilder()->build($quantifier);
            }
        };

        $dut->withoutShrinking()->run($quantifier);
    }
}
