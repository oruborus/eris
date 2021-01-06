<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\choose;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ReadmeTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function naturalNumbersMagnitude(): void
    {
        $this
            ->forAll(
                choose(0, 1000)
            )
            ->then(function (int $number): void {
                $this->assertLessThan(42, $number, "{$number} is apparently not less than 42.");
            });
    }
}
