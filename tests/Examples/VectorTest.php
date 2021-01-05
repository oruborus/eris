<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function count;
use function Eris\Generator\nat;
use function Eris\Generator\vector;
use function var_export;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class VectorTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function concatenationMaintainsLength(): void
    {
        $this
            ->forAll(
                vector(10, nat()),
                vector(10, nat())
            )
            ->then(function (array $first, array $second): void {
                $concatenated = array_merge($first, $second);

                $this->assertSame(
                    count($concatenated),
                    count($first) + count($second),
                    var_export($first, true) . " and " . var_export($second, true) .
                        " do not maintain their length when concatenated."
                );
            });
    }
}
