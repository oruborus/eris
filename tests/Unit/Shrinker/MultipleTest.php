<?php

declare(strict_types=1);

namespace Test\Unit\Shrinker;

use Eris\Generator\IntegerGenerator;
use Eris\Shrinker\Multiple;
use Eris\TimeLimit\FixedTimeLimit;
use Eris\TimeLimit\NoTimeLimit;
use Eris\Value\Value;
use RuntimeException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

use function count;

class MultipleTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Shrinker\Multiple::__construct
     * @covers Eris\Shrinker\Multiple::getTimeLimit
     * @covers Eris\Shrinker\Multiple::setTimeLimit
     *
     * @uses Eris\TimeLimit\FixedTimeLimit
     */
    public function timeLimitCanBeSet(): void
    {
        $dut = new Multiple([], function (): void {
        });

        $this->assertInstanceOf(NoTimeLimit::class, $dut->getTimeLimit());

        $dut->setTimeLimit(new FixedTimeLimit(10, '\time'));
        $this->assertInstanceOf(FixedTimeLimit::class, $dut->getTimeLimit());
    }

    /**
     * @test
     *
     * @covers Eris\Shrinker\Multiple::from
     * @covers Eris\Shrinker\Multiple::onAttempt
     * @covers Eris\Shrinker\Multiple::shrink
     *
     * @uses Eris\Shrinker\Multiple::__construct
     * @uses Eris\Shrinker\Multiple::checkGoodShrinkConditions
     *
     * @uses Eris\Generator\IntegerGenerator
     * @uses Eris\TimeLimit\NoTimeLimit
     * @uses Eris\Value\Value
     * @uses Eris\Value\ValueCollection
     *
     * @dataProvider provideInitiallyFailedTests
     */
    public function multipleBranchesConvergeFasterThanLinearShrinking(int $startingPoint): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that 5001 is equal to 5000 or is less than 5000.');

        $dut = new Multiple(
            [new IntegerGenerator()],
            function ($number): void {
                $this->assertLessThanOrEqual(5000, $number);
            }
        );

        $dut->onAttempt(function ($attempt) use (&$attempts) {
            $attempts[] = $attempt;
        });

        $attempts = [];
        $expected = 0.2 * ($startingPoint - 5000);

        $dut->from(
            new Value([$startingPoint], [new Value($startingPoint)]),
            new RuntimeException()
        );

        $this->assertLessThan($expected, count($attempts));
    }

    public function provideInitiallyFailedTests(): array
    {
        return [
            'starting point at 5500'   => [5500],
            'starting point at 6000'   => [6000],
            'starting point at 10000'  => [10000],
            'starting point at 100000' => [100000],
        ];
    }

    /**
     * @test
     *
     * @covers Eris\Shrinker\Multiple::from
     * @covers Eris\Shrinker\Multiple::onAttempt
     * @covers Eris\Shrinker\Multiple::shrink
     *
     * @uses Eris\Shrinker\Multiple::__construct
     * @uses Eris\Shrinker\Multiple::checkGoodShrinkConditions
     * @uses Eris\Shrinker\Multiple::setTimeLimit
     *
     * @uses Eris\Generator\IntegerGenerator
     * @uses Eris\TimeLimit\NoTimeLimit
     * @uses Eris\Value\Value
     * @uses Eris\Value\ValueCollection
     */
    public function throwsRuntimeExceptionWhenTimeLimitIsExceeded(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Eris has reached the time limit for shrinking (no time limit), " .
                "here it is presenting the simplest failure case." . PHP_EOL .
                "If you can afford to spend more time to find a simpler failing input, " .
                "increase it with the annotation \'@eris-shrink {seconds}\'."
        );

        $timeLimit = new class extends NoTimeLimit
        {
            public function hasBeenReached(): bool
            {
                return true;
            }
        };

        $dut = new Multiple([new IntegerGenerator()], function (): void {
        });
        $dut->setTimeLimit($timeLimit);

        $dut->from(
            new Value([5], [new Value(5)]),
            new RuntimeException()
        );
    }

    /**
     * @test
     *
     * @covers Eris\Shrinker\Multiple::addGoodShrinkCondition
     * @covers Eris\Shrinker\Multiple::checkGoodShrinkConditions
     * @covers Eris\Shrinker\Multiple::from
     *
     * @uses Eris\Shrinker\Multiple::__construct
     * @uses Eris\Shrinker\Multiple::shrink
     *
     * @uses Eris\Generator\IntegerGenerator
     * @uses Eris\TimeLimit\NoTimeLimit
     * @uses Eris\Value\Value
     * @uses Eris\Value\ValueCollection
     *
     * @dataProvider provideReturnValuesForConditions
     */
    public function additionalConditionsForGoodShrinkingCanBeSet(bool $return1, bool $return2): void
    {
        $this->expectException(RuntimeException::class);

        $condition1WasCalled = false;
        $condition1 = function (Value $value) use (&$condition1WasCalled, $return1): bool {
            $condition1WasCalled = true;
            return $return1;
        };

        $condition2WasCalled = false;
        $condition2 = function (Value $value) use (&$condition2WasCalled, $return2): bool {
            $condition2WasCalled = true;
            return $return2;
        };

        $dut = new Multiple([new IntegerGenerator()], function (): void {
        });

        $dut->addGoodShrinkCondition($condition1);
        $dut->addGoodShrinkCondition($condition2);

        $dut->from(
            new Value([5], [new Value(5)]),
            new RuntimeException()
        );

        $this->assertTrue($condition1WasCalled);
        $this->assertTrue($condition2WasCalled);
    }

    public function provideReturnValuesForConditions(): array
    {
        return [
            'both true' => [true, true],
            'one true, one false' => [true, false],
            'one false, one true' => [false, true],
            'both false' => [false, false],
        ];
    }
}
