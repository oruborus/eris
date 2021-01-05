<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\nat;
use function Eris\Generator\seq;
use function sort;
use function var_export;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SortTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function arraySorting(): void
    {
        $this
            ->forAll(
                seq(nat())
            )
            ->then(function (array $array): void {
                sort($array);
                for ($i = 0; $i < count($array) - 1; $i++) {
                    $this->assertGreaterThanOrEqual(
                        $array[$i],
                        $array[$i + 1],
                        "Array is not sorted: " . var_export($array, true)
                    );
                }
            });
    }
}
