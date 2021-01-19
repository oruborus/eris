<?php

declare(strict_types=1);

namespace Test\Unit\Shrinker;

use Eris\Shrinker\Multiple;
use Eris\Shrinker\ShrinkerFactory;
use Eris\TimeLimit\FixedTimeLimit;
use Eris\TimeLimit\NoTimeLimit;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ShrinkerFactoryTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Shrinker\ShrinkerFactory::__construct
     * @covers Eris\Shrinker\ShrinkerFactory::multiple
     * @covers Eris\Shrinker\ShrinkerFactory::configureShrinker
     *
     * @uses Eris\Shrinker\Multiple
     */
    public function createsMultipleWithoutTimeLimit(): void
    {
        $dut = new ShrinkerFactory();

        $actual = $dut->multiple([], static fn (bool $test): bool => !$test);

        $this->assertInstanceOf(Multiple::class, $actual);
        $this->assertInstanceOf(NoTimeLimit::class, $actual->getTimeLimit());
    }

    /**
     * @test
     *
     * @covers Eris\Shrinker\ShrinkerFactory::__construct
     * @covers Eris\Shrinker\ShrinkerFactory::multiple
     * @covers Eris\Shrinker\ShrinkerFactory::configureShrinker
     *
     * @uses Eris\Shrinker\Multiple
     * @uses Eris\TimeLimit\FixedTimeLimit
     */
    public function createsMultipleWithTimeLimit(): void
    {
        $dut = new ShrinkerFactory(2);

        $actual = $dut->multiple([], static fn (bool $test): bool => !$test);

        $this->assertInstanceOf(Multiple::class, $actual);
        $this->assertInstanceOf(FixedTimeLimit::class, $actual->getTimeLimit());
    }
}
