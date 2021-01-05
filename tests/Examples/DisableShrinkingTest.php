<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\nat;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class DisableShrinkingTest extends TestCase
{
    use TestTrait;

    private int $calls;

    /**
     * Shrinking may be avoided when then() is slow or non-deterministic.
     *
     * @test
     */
    public function thenIsNotCalledMultipleTime(): void
    {
        $this->calls = 0;
        $this
            ->forAll(
                nat()
            )
            ->disableShrinking()
            ->then(function (int $number): void {
                $this->calls++;

                $this->assertTrue(false, "Total calls: {$this->calls}");
            });
    }
}
