<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

use function array_sum;
use function Eris\Generator\map;
use function Eris\Generator\nat;
use function Eris\Generator\vector;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class MapTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function applyingAFunctionToValues(): void
    {
        $this
            ->forAll(
                vector(
                    3,
                    map(
                        static fn (int $n): int => 2 * $n,
                        nat()
                    )
                )
            )
            ->then(function (array $tripleOfEvenNumbers): void {
                foreach ($tripleOfEvenNumbers as $number) {
                    $this->assertSame(0, $number % 2, "The element of the vector {$number} is not even.");
                }
            });
    }

    /**
     * This test will fail because a positive integer will be certainly generated which - if doubled - is greater than 100.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function shrinkingJustMappedValues(): void
    {
        $this
            ->forAll(
                map(
                    static fn (int $n): int => 2 * $n,
                    nat()
                )
            )
            ->then(function (int $evenNumber): void {
                $this->assertLessThanOrEqual(100, $evenNumber);
            });
    }

    /**
     * This test will fail as there will certainly be three positive integers which sum is greater than 100.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function shrinkingMappedValuesInsideOtherGenerators(): void
    {
        $this->forAll(
            vector(
                3,
                map(
                    static fn (int $n): int => 2 * $n,
                    nat()
                )
            )
        )
            ->then(function (array $triple): void {
                $this->assertLessThanOrEqual(
                    100,
                    array_sum($triple),
                    "The triple sum {$triple[2]} + {$triple[2]} + {$triple[2]} is not less than 100."
                );
            });
    }

    // TODO: multiple generators means multiple values passed to map
}
