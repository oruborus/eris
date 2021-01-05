<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

use function bin2hex;
use function Eris\Generator\string;
use function Eris\Listener\log;
use function strlen;
use function var_export;

function concat(string $first, string $second): string
{
    if (strlen($second) > 5) {
        $second .= 'ERROR';
    }
    return $first . $second;
}

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class StringTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function rightIdentityElement(): void
    {
        $this
            ->forAll(
                string()
            )
            ->then(function (string $string): void {
                $this->assertEquals($string, concat($string, ''), "Concatenating '{$string}' to ''");
            });
    }

    /**
     * This tests fails for some generated strings as the length of the second string will be > 5.
     * The failing string will be caught and shrunk to its minimum failing value - in this case length of 6.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function lengthPreservation(): void
    {
        $this
            ->forAll(
                string(),
                string()
            )
            ->hook(log(sys_get_temp_dir() . '/eris-string-shrinking.log'))
            ->then(function (string $first, string $second): void {
                $result = concat($first, $second);

                $this->assertEquals(
                    strlen($first) + strlen($second),
                    strlen($result),
                    "Concatenating '{$first}' to '{$second}' gives '{$result}'" . PHP_EOL
                        . var_export($first, true) . PHP_EOL
                        . "strlen(): " . strlen($first) . PHP_EOL
                        . var_export($second, true) . PHP_EOL
                        . "strlen(): " . strlen($second) . PHP_EOL
                        . var_export($result, true) . PHP_EOL
                        . "strlen(): " . strlen($result) . PHP_EOL
                        . "First hex: " . var_export(bin2hex($first), true) . PHP_EOL
                        . "Second hex: " . var_export(bin2hex($second), true) . PHP_EOL
                        . "Result hex: " . var_export(bin2hex($result), true) . PHP_EOL
                );
            });
    }
}
