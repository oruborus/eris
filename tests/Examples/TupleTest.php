<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\choose;
use function Eris\Generator\elements;
use function Eris\Generator\tuple;
use function implode;
use function strlen;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TupleTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function concatenationMaintainsLength(): void
    {
        $this
            ->forAll(
                tuple(
                    elements("A", "B", "C"),
                    choose(0, 9)
                )
            )
            ->then(function (array $tuple): void {
                $actual = implode('', $tuple);
                $this->assertSame(2, strlen($actual), "{$actual} is not a 2-char string");
            });
    }
}
