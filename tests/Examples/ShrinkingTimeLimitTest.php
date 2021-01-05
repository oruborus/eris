<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\string;
use function sleep;
use function strlen;

function very_slow_concatenation(string $first, string $second): string
{
    if (strlen($second) > 10) {
        sleep(2);
        $second .= 'ERROR';
    }

    return $first . $second;
}

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ShrinkingTimeLimitTest extends TestCase
{
    use TestTrait;

    /**
     * This test will fail as the concatination will take 2 seconds.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function lengthPreservation(): void
    {
        $this
            ->shrinkingTimeLimit(2)
            ->forAll(
                string(),
                string()
            )
            ->then(function (string $first, string $second): void {
                $result = very_slow_concatenation($first, $second);

                $this->assertEquals(
                    strlen($first) + strlen($second),
                    strlen($result),
                    "Concatenating '{$first}' to '{$second}' gives '{$result}'"
                );
            });
    }

    /**
     * This test will fail as the concatination will take 2 seconds.
     *
     * @test
     *
     * @eris-shrink 2
     *
     * @throws ExpectationFailedException
     */
    public function lengthPreservationFromAnnotation(): void
    {
        $this
            ->forAll(
                string(),
                string()
            )
            ->then(function (string $first, string $second): void {
                $result = very_slow_concatenation($first, $second);

                $this->assertEquals(
                    strlen($first) + strlen($second),
                    strlen($result),
                    "Concatenating '{$first}' to '{$second}' gives '{$result}'"
                );
            });
    }
}
