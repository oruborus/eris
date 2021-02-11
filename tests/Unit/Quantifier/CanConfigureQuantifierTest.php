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
     * @covers Eris\Quantifier\CanConfigureQuantifier::limitTo
     *
     * @uses Eris\Quantifier\CanConfigureQuantifier::getQuantifierBuilder
     *
     * @uses Eris\Listener\TimeBasedTerminationCondition
     */
    public function canStageLimitsForQuantifierCreation(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $quantifier
            ->expects($this->once())
            ->method('stopOn')
            ->willReturnSelf();
        $quantifier
            ->expects($this->once())
            ->method('withMaximumIterations')
            ->willReturnSelf();

        $limit = new DateInterval('PT2S');

        $dut = new class()
        {
            use CanConfigureQuantifier {
                limitTo as public;
            }

            public function run(Quantifier $quantifier): void
            {
                $this->getQuantifierBuilder()->build($quantifier);
            }
        };

        $dut->limitTo($limit)->limitTo(15)->run($quantifier);
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
     * @covers Eris\Quantifier\CanConfigureQuantifier::withGrowth
     *
     * @uses Eris\Quantifier\CanConfigureQuantifier::getQuantifierBuilder
     */
    public function canStageGrowthTypeConditionsForQuantifierCreation(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $quantifier
            ->expects($this->once())
            ->method('withGrowth')
            ->willReturnSelf();

        $growth = $this->getMockForAbstractClass(Growth::class, [], '', false);

        $dut = new class()
        {
            use CanConfigureQuantifier {
                withGrowth as public;
            }

            public function run(Quantifier $quantifier): void
            {
                $this->getQuantifierBuilder()->build($quantifier);
            }
        };

        $dut->withGrowth($growth::class)->run($quantifier);
    }


    /**
     * @test
     *
     * @covers Eris\Quantifier\CanConfigureQuantifier::withSeed
     *
     * @uses Eris\Quantifier\CanConfigureQuantifier::getQuantifierBuilder
     */
    public function canStageSeedForQuantifierCreation(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $quantifier
            ->expects($this->once())
            ->method('withSeed')
            ->with(15)
            ->willReturnSelf();

        $dut = new class()
        {
            use CanConfigureQuantifier {
                withSeed as public;
            }

            public function run(Quantifier $quantifier): void
            {
                $this->getQuantifierBuilder()->build($quantifier);
            }
        };

        $dut->withSeed(15)->run($quantifier);
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


    /**
     * @test
     *
     * @covers Eris\Quantifier\CanConfigureQuantifier::withRand
     *
     * @uses Eris\Quantifier\CanConfigureQuantifier::getQuantifierBuilder
     */
    public function canStageRandTypeConditionsForQuantifierCreation(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $quantifier
            ->expects($this->once())
            ->method('withRand')
            ->willReturnSelf();

        $source = $this->getMockForAbstractClass(Source::class, [], '', false);

        $dut = new class()
        {
            use CanConfigureQuantifier {
                withRand as public;
            }

            public function run(Quantifier $quantifier): void
            {
                $this->getQuantifierBuilder()->build($quantifier);
            }
        };

        $dut->withRand($source::class)->run($quantifier);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\CanConfigureQuantifier::withShrinkingTimeLimit
     *
     * @uses Eris\Quantifier\CanConfigureQuantifier::getQuantifierBuilder
     */
    public function canStageShrinkingTimeLimitForQuantifierCreation(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $quantifier
            ->expects($this->once())
            ->method('withShrinkingTimeLimit')
            ->with(15)
            ->willReturnSelf();

        $dut = new class()
        {
            use CanConfigureQuantifier {
                withShrinkingTimeLimit as public;
            }

            public function run(Quantifier $quantifier): void
            {
                $this->getQuantifierBuilder()->build($quantifier);
            }
        };

        $dut->withShrinkingTimeLimit(15)->run($quantifier);
    }
}
