<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ChooseGenerator;
use Eris\Generator\VectorGenerator;

use function array_sum;
use function count;
use function rand;

/**
 * @uses Eris\Generator\AssociativeArrayGenerator
 * @uses Eris\Generator\boxAll
 * @uses Eris\Generator\TupleGenerator
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class VectorGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\VectorGenerator::__construct
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function generatesVectorWithGivenSizeAndElementsFromGivenGenerator(): void
    {
        $vectorSize = rand(5, 10);
        $dut = new VectorGenerator(
            $vectorSize,
            new ChooseGenerator(1, 10)
        );
        $vector = $dut($this->size, $this->rand);

        $this->assertSame($vectorSize, count($vector->value()));
        foreach ($vector->value() as $element) {
            $this->assertGreaterThanOrEqual(1, $element);
            $this->assertLessThanOrEqual(10, $element);
        }
    }

    /**
     * @test
     *
     * @coversNothing
     *
     * @uses Eris\Generator\VectorGenerator::__construct
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function shrinksElementsOfTheVector(): void
    {
        $dut = new VectorGenerator(
            rand(5, 10),
            new ChooseGenerator(1, 10)
        );

        $vector = $dut($this->size, $this->rand);

        $previousSum = array_sum($vector->value());

        for ($i = 0; $i < 15; $i++) {
            $vector = $dut->shrink($vector)->last();
            $currentSum = array_sum($vector->value());

            $this->assertLessThanOrEqual($previousSum, $currentSum);
            $previousSum = $currentSum;
        }
    }
}
