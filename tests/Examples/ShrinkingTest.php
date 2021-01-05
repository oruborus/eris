<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\choose;
use function Eris\Generator\string;
use function str_split;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ShrinkingTest extends TestCase
{
    use TestTrait;

    /**
     * This test fails as there will certainly be a string generated without the character 'B' (ex. '').
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function shrinkingAString(): void
    {
        $this
            ->forAll(
                string()
            )
            ->then(function (string $string): void {
                $this->assertNotContains('B', str_split($string));
            });
    }

    /**
     * This test will fail as there is no integer in the range [1, 20] which is divisible by 29.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function shrinkingRespectsAntecedents(): void
    {
        $this
            ->forAll(
                choose(1, 20)
            )
            ->when(function (int $number): bool {
                return $number > 10;
            })
            ->then(function (int $number): void {
                $this->assertSame(0, $number % 29, "The number {$number} is not multiple of 29");
            });
    }
}
