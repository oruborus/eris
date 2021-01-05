<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\regex;
use function strlen;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class RegexTest extends TestCase
{
    use TestTrait;

    /**
     * Note that * and + modifiers are not supported.
     * @see \Eris\Generator\regex in src\Generator\functions.php
     *
     * @test
     */
    public function stringsMatchingAParticularRegex(): void
    {
        $this
            ->forAll(
                regex("[a-z]{10}")
            )
            ->then(function (string $string): void {
                $this->assertEquals(10, strlen($string));
            });
    }
}
