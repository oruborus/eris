<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function array_sum;
use function var_export;
use function Eris\Generator\elements;
use function Eris\Generator\vector;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ElementsTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function elementsOnlyProducesElementsFromTheGivenArguments(): void
    {
        $this
            ->forAll(
                elements(1, 2, 3)
            )
            ->then(function (int $number): void {
                $this->assertContains($number, [1, 2, 3]);
            });
    }

    /**
     * This means you cannot have a Elements Generator with a single element,
     * which is perfectly fine as if you have a single element this generator
     * is useless. Use Constant Generator instead
     *
     * @test
     */
    public function elementsOnlyProducesElementsFromTheGivenArrayDomain()
    {
        $this
            ->forAll(
                elements([1, 2, 3])
            )
            ->then(function (int $number): void {
                $this->assertContains($number, [1, 2, 3]);
            });
    }

    /**
     * @test
     */
    public function vectorOfElementsGenerators(): void
    {
        $this
            ->forAll(
                vector(
                    4,
                    elements([2, 4, 6, 8, 10, 12])
                )
            )
            ->then(function (array $vector): void {
                $sum = array_sum($vector);

                $this->assertSame(
                    0,
                    $sum % 2,
                    "{$sum} is not even, but it's the sum of the vector " . var_export($vector, true)
                );
            });
    }
}
