<?php

declare(strict_types=1);

namespace Test\Unit;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Sample;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 */
class SampleTest extends TestCase
{
    /**
     * @var MockObject&Generator<int> $generator
     */
    private $generator;

    private RandomRange $rand;

    public function setUp(): void
    {
        $this->generator = $this->getMockForAbstractClass(Generator::class);
        $this->rand      = new RandomRange(new RandSource());
    }

    /**
     * @test
     *
     * @covers Eris\Sample::__construct
     * @covers Eris\Sample::collected
     * @covers Eris\Sample::of
     */
    public function hasNoCollectedValuesAfterCreation(): void
    {
        $dut = Sample::of($this->generator, $this->rand);

        $actual = $dut->collected();

        $this->assertEmpty($actual);
    }

    /**
     * @test
     *
     * @covers Eris\Sample::__construct
     * @covers Eris\Sample::collected
     * @covers Eris\Sample::of
     * @covers Eris\Sample::repeat
     */
    public function generatesSampleOfValues(): void
    {
        $times = 100;

        $this->generator
            ->expects($this->exactly($times))
            ->method('__invoke')
            ->willReturn(new Value(200));

        $dut = Sample::of($this->generator, $this->rand)->repeat($times);

        $actual = $dut->collected();

        $this->assertCount($times, $actual);
        $this->assertSame(array_fill(0, 100, 200), $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Sample::repeat
     *
     * @uses Eris\Sample::__construct
     * @uses Eris\Sample::collected
     * @uses Eris\Sample::of
     */
    public function generatesSampleOfValuesWithDefaultGeneratorSize(): void
    {
        $this->generator
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnCallback(
                static fn (int $argument): Value => new Value($argument)
            ));

        $dut = Sample::of($this->generator, $this->rand)->repeat(1);

        $actual = $dut->collected();

        $this->assertSame(Sample::DEFAULT_SIZE, $actual[0]);
    }

    /**
     * @test
     *
     * @covers Eris\Sample::repeat
     *
     * @uses Eris\Sample::__construct
     * @uses Eris\Sample::collected
     * @uses Eris\Sample::of
     */
    public function generatesSampleOfValuesWithSetGeneratorSize(): void
    {
        $generatorSize = 100;

        $this->generator
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnCallback(
                /**
                 * @return Value<int>
                 */
                static fn (int $argument): Value => new Value($argument)
            ));

        $dut = Sample::of($this->generator, $this->rand, $generatorSize)->repeat(1);

        $actual = $dut->collected();

        $this->assertSame($generatorSize, $actual[0]);
    }

    /**
     * @test
     *
     * @covers Eris\Sample::shrink
     *
     * @uses Eris\Sample::__construct
     * @uses Eris\Sample::collected
     * @uses Eris\Sample::of
     */
    public function generatesASingleValueAndShrinksItToItsMostReducedFormWhenCalledWithoutArgument(): void
    {
        $this->generator
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(new Value(4));

        $this->generator
            ->expects($this->any())
            ->method('shrink')
            ->willReturn(
                new ValueCollection([new Value(3)]),
                new ValueCollection([new Value(2)]),
                new ValueCollection([new Value(1)]),
                new ValueCollection([new Value(0)]),
                new ValueCollection([new Value(0)])
            );

        $dut = Sample::of($this->generator, $this->rand)->shrink();

        $actual = $dut->collected();

        $this->assertSame([4, 3, 2, 1, 0], $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Sample::shrink
     *
     * @uses Eris\Sample::__construct
     * @uses Eris\Sample::collected
     * @uses Eris\Sample::of
     */
    public function shrinksGivenArgumentToItsMostReducedForm(): void
    {
        $this->generator
            ->expects($this->any())
            ->method('shrink')
            ->willReturn(
                new ValueCollection([new Value(3)]),
                new ValueCollection([new Value(2)]),
                new ValueCollection([new Value(1)]),
                new ValueCollection([new Value(0)]),
                new ValueCollection([new Value(0)])
            );

        $dut = Sample::of($this->generator, $this->rand)->shrink(new Value(4));

        $actual = $dut->collected();

        $this->assertSame([4, 3, 2, 1, 0], $actual);
    }
}
