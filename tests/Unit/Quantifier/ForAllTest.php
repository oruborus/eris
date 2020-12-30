<?php

declare(strict_types=1);

namespace Test\Unit\Quantifier;

use Eris\Contracts\Generator;
use Eris\Contracts\Listener;
use Eris\Growth\TriangularGrowth;
use Eris\Quantifier\ForAll;
use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Shrinker\ShrinkerFactory;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ForAllTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::withMaxSize
     * @covers Eris\Quantifier\ForAll::getMaxSize
     *
     * @uses Eris\Quantifier\ForAll::__construct
     *
     * @uses Eris\Contracts\Growth
     * @uses Eris\Growth\TriangularGrowth
     * @uses Eris\Random\RandomRange
     * @uses Eris\Shrinker\ShrinkerFactory
     */
    public function maximumSizeCanBeChanged(): void
    {
        $sizes = new TriangularGrowth(ForAll::DEFAULT_MAX_SIZE, 100);

        $dut = new ForAll([], $sizes, [new ShrinkerFactory([]), 'multiple'], new RandomRange(new RandSource()));

        $this->assertLessThanOrEqual(ForAll::DEFAULT_MAX_SIZE, $dut->getMaxSize());
        $this->assertInstanceOf(ForAll::class, $dut->withMaxSize(50));
        $this->assertLessThanOrEqual(50, $dut->getMaxSize());
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::withIterations
     * @covers Eris\Quantifier\ForAll::getIterations
     *
     * @uses Eris\Quantifier\ForAll::__construct
     *
     * @uses Eris\Contracts\Growth
     * @uses Eris\Growth\TriangularGrowth
     * @uses Eris\Random\RandomRange
     * @uses Eris\Shrinker\ShrinkerFactory
     */
    public function iterationsCanBeChanged(): void
    {
        $sizes = new TriangularGrowth(ForAll::DEFAULT_MAX_SIZE, 100);

        $dut = new ForAll([], $sizes, [new ShrinkerFactory([]), 'multiple'], new RandomRange(new RandSource()));

        $this->assertSame(100, $dut->getIterations());
        $this->assertInstanceOf(ForAll::class, $dut->withIterations(50));
        $this->assertSame(50, $dut->getIterations());
    }

    /**
     * @test
     *
     * @covers Eris\Quantifier\ForAll::__invoke
     * @covers Eris\Quantifier\ForAll::__construct
     *
     * @uses Eris\Quantifier\ForAll::antecedentsAreSatisfied
     * @uses Eris\Quantifier\ForAll::getIterations
     * @uses Eris\Quantifier\ForAll::hook
     * @uses Eris\Quantifier\ForAll::notifyListeners
     * @uses Eris\Quantifier\ForAll::terminationConditionsAreSatisfied
     *
     * @uses Eris\Contracts\Growth
     * @uses Eris\Generator\TupleGenerator
     * @uses Eris\Generator\ensureAreAllGenerators
     * @uses Eris\Generator\ensureIsGenerator
     * @uses Eris\Generator\TupleGenerator
     * @uses Eris\Growth\TriangularGrowth
     * @uses Eris\Random\RandomRange
     * @uses Eris\Shrinker\Multiple
     * @uses Eris\Shrinker\ShrinkerFactory
     * @uses Eris\TimeLimit\NoTimeLimit
     * @uses Eris\Value\Value
     * @uses Eris\Value\ValueCollection
     *
     * @dataProvider provideMethodNamesAndConstructorArguments
     */
    public function callsListenersMethods(string $name, int $count, array $arguments, $assertion): void
    {
        $listener1 = $this->getMockForAbstractClass(Listener::class);
        $listener1->expects($this->exactly($count))->method($name);

        $listener2 = $this->getMockForAbstractClass(Listener::class);
        $listener2->expects($this->exactly($count))->method($name);

        $forAll = (new ForAll(...$arguments))
            ->hook($listener1)
            ->hook($listener2);

        try {
            $forAll($assertion);
        } catch (AssertionFailedError $e) {
        }
    }

    public function provideMethodNamesAndConstructorArguments(): array
    {
        $generator = new class () implements Generator
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

        $sizes = new TriangularGrowth(ForAll::DEFAULT_MAX_SIZE, 100);


        return [
            'startPropertyVerification' => [
                'startPropertyVerification',
                1,
                [[], $sizes, [new ShrinkerFactory([]), 'multiple'], new RandomRange(new RandSource())],
                fn (): bool => false
            ],
            'newGeneration' => [
                'newGeneration',
                100,
                [[], $sizes, [new ShrinkerFactory([]), 'multiple'], new RandomRange(new RandSource())],
                fn (): bool => false
            ],
            'failure' => [
                'failure',
                1,
                [[$generator], $sizes, [new ShrinkerFactory(['timeLimit' => null]), 'multiple'], new RandomRange(new RandSource())],
                function () {
                    throw new AssertionFailedError();
                }
            ],
            'shrinking' => [
                'shrinking',
                1,
                [[$generator], $sizes, [new ShrinkerFactory(['timeLimit' => null]), 'multiple'], new RandomRange(new RandSource())],
                function () {
                    throw new AssertionFailedError();
                }
            ],
            'endPropertyVerification' => [
                'endPropertyVerification',
                1,
                [[], $sizes, [new ShrinkerFactory([]), 'multiple'], new RandomRange(new RandSource())],
                fn (): bool => false
            ],
        ];
    }
}
