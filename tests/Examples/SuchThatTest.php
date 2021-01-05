<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\ExpectationFailedException;

use function count;
use function Eris\Generator\choose;
use function Eris\Generator\filter;
use function Eris\Generator\int;
use function Eris\Generator\oneOf;
use function Eris\Generator\seq;
use function Eris\Generator\suchThat;
use function Eris\Generator\string;
use function Eris\Generator\vector;
use function Eris\Listener\log;
use function sys_get_temp_dir;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SuchThatTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function suchThatBuildsANewGeneratorFilteringTheInnerOne(): void
    {
        $this
            ->forAll(
                vector(
                    5,
                    suchThat(
                        static fn (int $n): bool => $n > 42,
                        choose(0, 1000)
                    )
                )
            )
            ->then($this->allNumbersAreBiggerThan(42));
    }

    /**
     * @test
     */
    public function filterSyntax(): void
    {
        $this
            ->forAll(
                vector(
                    5,
                    filter(
                        static fn (int $n): bool => $n > 42,
                        choose(0, 1000)
                    )
                )
            )
            ->then($this->allNumbersAreBiggerThan(42));
    }

    /**
     * This test fails as there are possible values which pass the given PHPUnit constraint.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function suchThatAcceptsPHPUnitConstraints(): void
    {
        $this
            ->forAll(
                vector(
                    5,
                    suchThat(
                        $this->isType('integer'),
                        oneOf(
                            choose(0, 1000),
                            string()
                        )
                    )
                )
            )
            ->hook(log(sys_get_temp_dir() . '/eris-such-that.log'))
            ->then($this->allNumbersAreBiggerThan(42));
    }

    /**
     * This test fails as there are integers between 42 and 100.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function suchThatShrinkingRespectsTheCondition(): void
    {
        $this
            ->forAll(
                suchThat(
                    static fn (int $n): bool => $n > 42,
                    choose(0, 1000)
                )
            )
            ->then(function (int $number): void {
                $this->assertGreaterThan(100, $number);
            });
    }

    /**
     * This test fails as there are integers less than 100 in the available interval.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function suchThatShrinkingRespectsTheConditionButTriesToSkipOverTheNotAllowedSet(): void
    {
        $this
            ->forAll(
                suchThat(
                    static fn (int $n): bool => $n !== 42,
                    choose(0, 1000)
                )
            )
            ->then(function (int $number): void {
                $this->assertGreaterThan(100, $number);
            });
    }

    /**
     * @test
     */
    public function suchThatAvoidingTheEmptyListDoesNotGetStuckOnASmallGeneratorSize(): void
    {
        $this
            ->forAll(
                suchThat(
                    static fn (array $ints): bool => count($ints) > 0,
                    seq(int())
                )
            )
            ->then(function (array $ints): void {
                $this->assertGreaterThanOrEqual(1, count($ints));
            });
    }

    /**
     * @return callable(int[]): void
     */
    private function allNumbersAreBiggerThan(int $lowerLimit)
    {
        return function (array $vector) use ($lowerLimit) {
            foreach ($vector as $number) {
                $this->assertGreaterThan($lowerLimit, $number);
            }
        };
    }
}
