<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function Eris\Generator\string;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ErrorTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     *
     * @throws RuntimeException
     */
    public function genericExceptionsDoNotShrinkButStillShowTheInput(): void
    {
        $this
            ->forAll(
                string()
            )
            ->then(function (string $string): void {
                throw new RuntimeException("Something like a missing array index happened.");
            });
    }
}
