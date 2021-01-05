<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\bool;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class BooleanTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function booleanValueIsTrueOrFalse(): void
    {
        $this
            ->forAll(
                bool()
            )
            ->then(function (bool $value): void {
                $this->assertTrue(($value === true || $value === false), "{$value} is not true nor false");
            });
    }
}
