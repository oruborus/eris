<?php

declare(strict_types=1);

namespace Test\Unit\Quantifier;

use DateInterval;
use Eris\Contracts\Antecedent;
use Eris\Contracts\Generator;
use Eris\Contracts\Growth;
use Eris\Contracts\Listener;
use Eris\Contracts\Source;
use Eris\Contracts\TerminationCondition;
use Eris\Generator\GeneratorCollection;
use Eris\Generator\SkipValueException;
use Eris\Growth\LinearGrowth;
use Eris\Growth\TriangularGrowth;
use Eris\Listener\MinimumEvaluations;
use Eris\Quantifier\ForAll;
use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Test\Support\Listener\Spy;
use Test\Support\TerminationCondition\TerminationSwitch;

use function putenv;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @uses Eris\Contracts\Collection
 * @uses Eris\Contracts\Growth
 * @uses Eris\Antecedent\AntecedentCollection
 * @uses Eris\Generator\GeneratorCollection
 * @uses Eris\Listener\ListenerCollection
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Shrinker\Multiple
 * @uses Eris\Shrinker\ShrinkerFactory
 * @uses Eris\TerminationCondition\TerminationConditionCollection
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 */
class ForAllTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        putenv('ERIS_ORIGINAL_INPUT');
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::withMaximumSize
     * @covers Eris\Quantifier\ForAll::getMaximumSize
     *
     * @uses Eris\Quantifier\ForAll::__construct
     */
    public function maximumSizeCanBeChanged(): void
    {
        $dut = new ForAll(new GeneratorCollection());

        $this->assertLessThanOrEqual(ForAll::DEFAULT_MAX_SIZE, $dut->getMaximumSize());
        $this->assertInstanceOf(ForAll::class, $dut->withMaximumSize(50));
        $this->assertLessThanOrEqual(50, $dut->getMaximumSize());
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::withMaximumIterations
     * @covers Eris\Quantifier\ForAll::getMaximumIterations
     *
     * @uses Eris\Quantifier\ForAll::__construct
     */
    public function iterationsCanBeChanged(): void
    {
        $dut = new ForAll(new GeneratorCollection());

        $this->assertInstanceOf(ForAll::class, $dut->withMaximumIterations(50));
        $this->assertSame(50, $dut->getMaximumIterations());
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::limitTo
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::getMaximumIterations
     * @uses Eris\Quantifier\ForAll::withMaximumIterations
     */
    public function iterationsCanBeChangedAsLimit(): void
    {
        $dut = new ForAll(new GeneratorCollection());
        $dut->limitTo(50);

        $this->assertSame(50, $dut->getMaximumIterations());
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::then
     * @covers Eris\Quantifier\ForAll::__construct
     *
     * @uses Eris\Quantifier\ForAll::getMaximumIterations
     * @uses Eris\Quantifier\ForAll::listenTo
     *
     * @uses Eris\Generator\TupleGenerator
     * @uses Eris\Growth\TriangularGrowth
     * @uses Eris\TimeLimit\NoTimeLimit
     *
     * @dataProvider provideMethodNamesAndConstructorArguments
     *
     * @param callable(mixed...):void $assertion
     */
    public function callsListenersMethods(string $name, int $count, GeneratorCollection $generators, $assertion): void
    {
        $listener1 = $this->getMockForAbstractClass(Listener::class);
        $listener1->expects($this->exactly($count))->method($name);

        $listener2 = $this->getMockForAbstractClass(Listener::class);
        $listener2->expects($this->exactly($count))->method($name);

        $forAll = (new ForAll($generators))
            ->listenTo($listener1)
            ->listenTo($listener2);

        try {
            $forAll($assertion);
        } catch (AssertionFailedError $e) {
        }
    }

    /**
     * @psalm-suppress InternalClass
     */
    public function provideMethodNamesAndConstructorArguments(): array
    {
        $generator = new class() implements Generator
        {
            public function __invoke(int $size, RandomRange $rand): value
            {
                return new Value(1);
            }

            public function shrink(Value $value): ValueCollection
            {
                return new ValueCollection([new Value(0)]);
            }
        };

        return [
            'startPropertyVerification' => [
                'startPropertyVerification',
                1,
                new GeneratorCollection(),
                static function (): void {
                }
            ],
            'newGeneration' => [
                'newGeneration',
                100,
                new GeneratorCollection(),
                static function (): void {
                }
            ],
            'failure' => [
                'failure',
                1,
                new GeneratorCollection($generator),
                static function (): void {
                    throw new AssertionFailedError();
                }
            ],
            'shrinking' => [
                'shrinking',
                1,
                new GeneratorCollection($generator),
                static function (): void {
                    throw new AssertionFailedError();
                }
            ],
            'endPropertyVerification' => [
                'endPropertyVerification',
                1,
                new GeneratorCollection(),
                static function (): void {
                }
            ],
        ];
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::then
     * @covers Eris\Quantifier\ForAll::stopOn
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::listenTo
     *
     * @uses Eris\Growth\TriangularGrowth
     */
    public function canBeTerminatedWithConditions(): void
    {
        $condition = $this->createMock(TerminationCondition::class);
        $condition->expects($this->once())->method('shouldTerminate')->willReturn(true);

        $listener = $this->createMock(Listener::class);
        $listener
            ->expects($this->once())
            ->method('endPropertyVerification')
            ->with(0, ForAll::DEFAULT_MAX_ITERATIONS, null);

        $dut = new ForAll(new GeneratorCollection());
        $dut->stopOn($condition);
        $dut->listenTo($listener);

        $dut->then(static fn (): bool => true);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::then
     * @covers Eris\Quantifier\ForAll::stopOn
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::listenTo
     *
     * @uses Eris\Growth\TriangularGrowth
     */
    public function restartsTheGenerationProcessOnSkipValueExceptionOfGenerator(): void
    {
        $condition = $this->createMock(TerminationCondition::class);
        $condition->expects($this->exactly(2))->method('shouldTerminate')->willReturn(false, true);

        $listener = $this->createMock(Listener::class);
        $listener
            ->expects($this->once())
            ->method('endPropertyVerification')
            ->with(0, ForAll::DEFAULT_MAX_ITERATIONS, null);

        $generator = $this->createStub(Generator::class);
        $generator->method('__invoke')->willThrowException(new SkipValueException());

        $dut = new ForAll(new GeneratorCollection($generator));
        $dut->stopOn($condition);
        $dut->listenTo($listener);

        $dut->then(static fn (): bool => true);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::then
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::listenTo
     * @uses Eris\Quantifier\ForAll::stopOn
     * @uses Eris\Quantifier\ForAll::when
     *
     * @uses Eris\Growth\TriangularGrowth
     */
    public function restartsTheGenerationProcessWhenAntecedentsAreNotSatisfied(): void
    {
        $condition = $this->createMock(TerminationCondition::class);
        $condition->expects($this->exactly(2))->method('shouldTerminate')->willReturn(false, true);

        $listener = $this->createMock(Listener::class);
        $listener
            ->expects($this->once())
            ->method('endPropertyVerification')
            ->with(0, ForAll::DEFAULT_MAX_ITERATIONS, null);

        $antecedent = $this->createMock(Antecedent::class);
        $antecedent->expects($this->once())->method('evaluate')->willReturn(false);

        $dut = new ForAll(new GeneratorCollection());
        $dut->stopOn($condition);
        $dut->listenTo($listener);
        $dut->when($antecedent);

        $dut->then(static fn (): bool => true);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::then
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::listenTo
     *
     * @uses Eris\Growth\TriangularGrowth
     */
    public function whenAssertionSucceedsAllIterationsAreCompleted(): void
    {
        $listener = $this->createMock(Listener::class);
        $listener
            ->expects($this->once())
            ->method('endPropertyVerification')
            ->with(ForAll::DEFAULT_MAX_ITERATIONS, ForAll::DEFAULT_MAX_ITERATIONS, null);


        $dut = new ForAll(new GeneratorCollection());
        $dut->listenTo($listener);

        $dut->then(static fn (): bool => true);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::then
     * @covers Eris\Quantifier\ForAll::withoutShrinking
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::listenTo
     *
     * @uses Eris\Growth\TriangularGrowth
     * @uses Eris\TimeLimit\NoTimeLimit
     *
     * @psalm-suppress InternalClass
     */
    public function doesNotShrinkWhenShrinkingIsDisabled(): void
    {
        $this->expectException(AssertionFailedError::class);

        $listener = $this->createMock(Listener::class);
        $listener
            ->expects($this->once())
            ->method('endPropertyVerification')
            ->with(1, ForAll::DEFAULT_MAX_ITERATIONS, $this->isInstanceOf(AssertionFailedError::class));
        $listener
            ->expects($this->once())
            ->method('failure');
        $listener
            ->expects($this->never())
            ->method('shrinking');

        $generator = $this->createStub(Generator::class);
        $generator->method('__invoke')->willReturn(new Value(5));
        $generator->method('shrink')->willReturn(new ValueCollection([new Value(4)]));

        $dut = new ForAll(new GeneratorCollection($generator));
        $dut->listenTo($listener);
        $dut->withoutShrinking();

        $dut->then(static function (): bool {
            throw new AssertionFailedError();
        });
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::withGrowth
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::then
     * @uses Eris\Quantifier\ForAll::listenTo
     *
     * @uses Eris\Growth\LinearGrowth
     * @uses Eris\Growth\TriangularGrowth
     * @uses Eris\TimeLimit\NoTimeLimit
     *
     * @dataProvider provideGrowth
     *
     * @param string|class-string<Growth> $growth
     */
    public function differentGenerationGrowthsCanBeSet(string $growth, Growth $growthInstance): void
    {
        $generator = $this->createStub(Generator::class);
        $generator->method('__invoke')->willReturnCallback(static fn (int $value): Value => new Value($value));

        $assertion = function (array $value, int $iteration) use ($growthInstance): void {
            $this->assertSame($value[0], $growthInstance[$iteration]);
        };

        $listener = new Spy();
        $listener->setNewGenerationAssertion($assertion);

        $dut = new ForAll(new GeneratorCollection($generator));
        $dut->listenTo($listener);
        $dut->withGrowth($growth);

        $dut->then(static fn (): bool => true);
    }

    public function provideGrowth(): array
    {
        return [
            'triangular growth' => [
                TriangularGrowth::class,
                new TriangularGrowth(ForAll::DEFAULT_MAX_SIZE, ForAll::DEFAULT_MAX_ITERATIONS)
            ],
            'triangular growth string' => [
                'triangular',
                new TriangularGrowth(ForAll::DEFAULT_MAX_SIZE, ForAll::DEFAULT_MAX_ITERATIONS)
            ],
            'linear growth' => [
                LinearGrowth::class,
                new LinearGrowth(ForAll::DEFAULT_MAX_SIZE, ForAll::DEFAULT_MAX_ITERATIONS)
            ],
            'linear growth string' => [
                'linear',
                new LinearGrowth(ForAll::DEFAULT_MAX_SIZE, ForAll::DEFAULT_MAX_ITERATIONS)
            ],
        ];
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::withGrowth
     *
     * @uses Eris\Quantifier\ForAll::__construct
     */
    public function throwsWhenNonExistingGrowthTypeIsSelected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $dut = new ForAll(new GeneratorCollection());

        $dut->withGrowth('THIS_GROWTH_TYPE_DOES_NOT_EXIST!!!');
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::withRand
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::then
     * @uses Eris\Quantifier\ForAll::listenTo
     *
     * @uses Eris\Growth\LinearGrowth
     * @uses Eris\Growth\TriangularGrowth
     * @uses Eris\TimeLimit\NoTimeLimit
     *
     * @dataProvider provideRand
     *
     * @param string|class-string<Source> $growth
     */
    public function differentGenerationRandsCanBeSet(string $source, Source $sourceInstance): void
    {
        $generator = $this->createStub(Generator::class);
        $generator->method('__invoke')->willReturnCallback(
            static fn (int $_, RandomRange $randomRange): Value => new Value($randomRange)
        );

        $assertion = function (array $value, int $iteration) use ($sourceInstance): void {
            $this->assertEquals($value[0], new RandomRange($sourceInstance));
        };

        $listener = new Spy();
        $listener->setNewGenerationAssertion($assertion);

        $dut = new ForAll(new GeneratorCollection($generator));
        $dut->listenTo($listener);
        $dut->withRand($source);

        $dut->then(static fn (): bool => true);
    }

    public function provideRand(): array
    {
        return [
            'rand source' => [RandSource::class, new RandSource()],
            'rand source string' => ['rand', new RandSource()],
        ];
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::withRand
     *
     * @uses Eris\Quantifier\ForAll::__construct
     */
    public function throwsWhenNonExistingRandTypeIsSelected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $dut = new ForAll(new GeneratorCollection());

        $dut->withRand('THIS_RAND_TYPE_DOES_NOT_EXIST!!!');
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::withSeed
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::then
     * @uses Eris\Quantifier\ForAll::listenTo
     * @uses Eris\Quantifier\ForAll::stopOn
     *
     * @uses Eris\Growth\LinearGrowth
     * @uses Eris\Growth\TriangularGrowth
     * @uses Eris\TimeLimit\NoTimeLimit
     */
    public function seedCanBeSet(): void
    {
        $seed = 1234567890;

        $randomRange = new RandomRange(new RandSource());
        $randomRange->seed($seed);
        $expected = $randomRange->rand();
        $randomRange->seed(0);

        $generator = $this->createStub(Generator::class);
        $generator->method('__invoke')->willReturnCallback(
            static fn (int $_, RandomRange $randomRange): Value => new Value($randomRange)
        );

        $switch = new TerminationSwitch();

        $assertion = function (array $value, int $iteration) use ($expected, $switch): void {
            $switch->abort();

            $this->assertInstanceOf(RandomRange::class, $value[0]);
            $this->assertSame($expected, $value[0]->rand());
        };

        $listener = new Spy();
        $listener->setNewGenerationAssertion($assertion);

        $dut = new ForAll(new GeneratorCollection($generator));
        $dut->listenTo($listener);
        $dut->stopOn($switch);

        $dut->withSeed($seed);

        $dut->then(static fn (): bool => true);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::then
     *
     * @uses Eris\Quantifier\ForAll::__construct
     *
     * @uses Eris\Growth\TriangularGrowth
     */
    public function generatedInputWillBeShownWhenCorrespondingEnvironmentFlagIsSet(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(-1);
        $this->expectExceptionMessage(
            "Original input: " .
                "Eris\Value\Value::__set_state(array(\n   'value' => \n  array (\n  ),\n   'input' => \n  array (\n  ),\n))" .
                PHP_EOL . "Possibly shrunk input follows." . PHP_EOL
        );

        putenv('ERIS_ORIGINAL_INPUT=1');

        $dut = new ForAll(new GeneratorCollection());

        $dut->then(static function (): void {
            throw new Exception();
        });
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::listenTo
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::then
     * @uses Eris\Quantifier\ForAll::stopOn
     *
     * @uses Eris\Growth\TriangularGrowth
     */
    public function listenersCanBeAdded(): void
    {
        $listener1 = $this->createMock(MinimumEvaluations::class);
        $listener1->expects($this->never())->method('startPropertyVerification');
        $listener2 = $this->createMock(MinimumEvaluations::class);
        $listener2->expects($this->once())->method('startPropertyVerification');
        $listener3 = $this->getMockForAbstractClass(Listener::class);
        $listener3->expects($this->once())->method('startPropertyVerification');

        $switch = new TerminationSwitch();
        $switch->abort();

        $dut = new ForAll(new GeneratorCollection());
        $dut->listenTo($listener1);
        $dut->listenTo($listener2);
        $dut->listenTo($listener3);
        $dut->stopOn($switch);

        $dut->then(static fn (): bool => true);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::limitTo
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::listenTo
     * @uses Eris\Quantifier\ForAll::stopOn
     * @uses Eris\Quantifier\ForAll::then
     *
     * @uses Eris\Growth\TriangularGrowth
     * @uses Eris\TerminationCondition\TimeBasedTerminationCondition
     */
    public function timeIntervalCanBeUsedAsLimit(): void
    {
        $interval = new DateInterval('PT0S');
        $listener = $this->getMockForAbstractClass(Listener::class);
        $listener->expects($this->never())->method('newGeneration');

        $dut = new ForAll(new GeneratorCollection());
        $dut->listenTo($listener);

        $dut->limitTo($interval);

        $dut->then(static fn (): bool => true);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::when
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::then
     * @uses Eris\Quantifier\ForAll::stopOn
     *
     * @uses Eris\Antecedent\IndependentConstraintsAntecedent
     * @uses Eris\Growth\TriangularGrowth
     */
    public function constraintsCanBeUsedAsAntecedent(): void
    {
        $generator = $this->getMockForAbstractClass(Generator::class);
        $generator->method('__invoke')->willReturn(new Value(5));

        $switch = $this->getMockForAbstractClass(TerminationCondition::class);
        $switch->method('shouldTerminate')->willReturn(false, true);

        $constraint = $this->createMock(Constraint::class);
        $constraint->expects($this->once())->method('evaluate')->willReturn(true);

        $dut = new ForAll(new GeneratorCollection($generator));
        $dut->stopOn($switch);

        $dut->when($constraint);

        $dut->then(static fn (): bool => true);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::when
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::then
     * @uses Eris\Quantifier\ForAll::stopOn
     *
     * @uses Eris\Growth\TriangularGrowth
     */
    public function antecedentsCanBeUsedAsAntecedent(): void
    {
        $generator = $this->getMockForAbstractClass(Generator::class);
        $generator->method('__invoke')->willReturn(new Value(5));

        $switch = $this->getMockForAbstractClass(TerminationCondition::class);
        $switch->method('shouldTerminate')->willReturn(false, true);

        $antecedent = $this->createMock(Antecedent::class);
        $antecedent->expects($this->once())->method('evaluate')->willReturn(true);

        $dut = new ForAll(new GeneratorCollection($generator));
        $dut->stopOn($switch);

        $dut->when($antecedent);

        $dut->then(static fn (): bool => true);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::when
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::then
     * @uses Eris\Quantifier\ForAll::stopOn
     *
     * @uses Eris\Antecedent\SingleCallbackAntecedent
     * @uses Eris\Growth\TriangularGrowth
     */
    public function callbacksCanBeUsedAsAntecedent(): void
    {
        $generator = $this->getMockForAbstractClass(Generator::class);
        $generator->method('__invoke')->willReturn(new Value(5));

        $switch = $this->getMockForAbstractClass(TerminationCondition::class);
        $switch->method('shouldTerminate')->willReturn(false, true);

        $callback = function (): bool {
            $this->assertTrue(true);

            return true;
        };

        $dut = new ForAll(new GeneratorCollection($generator));
        $dut->stopOn($switch);

        $dut->when($callback);

        $dut->then(static fn (): bool => true);
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::withShrinkingTimeLimit
     *
     * @uses Eris\Quantifier\ForAll::__construct
     * @uses Eris\Quantifier\ForAll::then
     *
     * @uses Eris\Growth\TriangularGrowth
     * @uses Eris\TimeLimit\FixedTimeLimit
     *
     * @psalm-suppress InternalClass
     */
    public function timelimitForShrinkingCanBeSet(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Eris has reached the time limit for shrinking (0s elapsed of 0s), " .
                "here it is presenting the simplest failure case." . PHP_EOL .
                "If you can afford to spend more time to find a simpler failing input, " .
                "increase it with the annotation \'@eris-shrink {seconds}\'."
        );

        $generator = $this->getMockForAbstractClass(Generator::class);
        $generator->method('__invoke')->willReturn(new Value(5));
        $generator->method('shrink')->willReturn(new ValueCollection([new Value(4)]));

        $dut = new ForAll(new GeneratorCollection($generator));
        $dut->withShrinkingTimeLimit(0);

        $flag = true;

        $dut->then(function () use (&$flag): void {
            if ($flag) {
                $flag = false;
                throw new AssertionFailedError();
            }

            $flag = true;
        });
    }
}
