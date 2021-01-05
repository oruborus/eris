<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function count;
use function Eris\Generator\char;
use function Eris\Generator\int;
use function Eris\Generator\nat;
use function Eris\Generator\neg;
use function Eris\Generator\seq;
use function Eris\Generator\vector;
use function Eris\Listener\collectFrequencies;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @todo Handle output of CollectFrequencies
 */
class CollectTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function generatedDataCollectionOnScalars(): void
    {
        $this
            ->forAll(
                neg()
            )
            ->hook(collectFrequencies())
            ->then(function (int $x): void {
                $this->assertTrue($x < $x + 1);
            });
    }

    /**
     * @test
     */
    public function generatedDataCollectionOnMoreComplexDataStructures()
    {
        $this
            ->forAll(
                vector(2, int()),
                char()
            )
            ->hook(collectFrequencies())
            ->then(function (array $vector): void {
                $this->assertCount(2, $vector);
            });
    }

    /**
     * @test
     */
    public function generatedDataCollectionWithCustomMapper(): void
    {
        $this
            ->forAll(
                seq(nat())
            )
            ->withMaxSize(10)
            ->hook(collectFrequencies(fn (array $array): int => count($array)))
            ->then(function (array $array) {
                $this->assertSame(count($array), count(array_reverse($array)));
            });
    }
}
