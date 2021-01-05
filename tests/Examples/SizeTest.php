<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\ExpectationFailedException;

use function Eris\Generator\int;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SizeTest extends TestCase
{
    use TestTrait;

    /**
     * With the default sizes this test would pass, as numbers greater  than or equal to 100,000
     * would never be reached.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function maxSizeCanBeIncreased(): void
    {
        $this
            ->forAll(
                int()
            )
            ->withMaxSize(1000 * 1000)
            ->then(function (int $number): void {
                $this->assertLessThan(100 * 1000, $number);
            });
    }
}
