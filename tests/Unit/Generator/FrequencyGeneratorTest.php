<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ChooseGenerator;
use Eris\Generator\FrequencyGenerator;
use InvalidArgumentException;

use function abs;

/**
 * @uses Eris\Generator\box
 * @uses Eris\Generator\ConstantGenerator
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FrequencyGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\FrequencyGenerator::__construct
     * @covers Eris\Generator\FrequencyGenerator::__invoke
     */
    public function equalProbability(): void
    {
        $dut = new FrequencyGenerator([
            [1, 42],
            [1, 21],
        ]);

        $countOf = $this->distribute($dut);

        $this->assertLessThan(
            100,
            abs($countOf[42] - $countOf[21]),
            'Generators have the same frequency but one is chosen more often than the other: ' . var_export($countOf, true)
        );
    }

    /**
     * @test
     *
     * @covers Eris\Generator\FrequencyGenerator::__construct
     * @covers Eris\Generator\FrequencyGenerator::__invoke
     */
    public function moreFrequentGeneratorIsChosenMoreOften(): void
    {
        $dut = new FrequencyGenerator([
            [10, 42],
            [1, 21],
        ]);

        $countOf = $this->distribute($dut);

        $this->assertLessThan(
            $countOf[42],
            $countOf[21],
            '21 got chosen more often then 42 even if it has a much lower frequency'
        );
    }

    /**
     * @test
     *
     * @covers Eris\Generator\FrequencyGenerator::__construct
     * @covers Eris\Generator\FrequencyGenerator::__invoke
     */
    public function zeroFrequencyMeansItWillNotBeChosen(): void
    {
        $dut = new FrequencyGenerator([
            [0, 42],
            [1, 21],
        ]);

        $countOf = $this->distribute($dut);

        $this->assertArrayNotHasKey(42, $countOf);
        $this->assertSame(1000, $countOf[21]);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\FrequencyGenerator::__construct
     */
    public function thrwosExceptionWhenConstructedWithEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new FrequencyGenerator([]);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\FrequencyGenerator::__construct
     */
    public function thrwosExceptionWhenSumOfFrequenciesIsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new FrequencyGenerator([[0, 1], [0, 2], [0, 3]]);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\FrequencyGenerator::__construct
     */
    public function throwsExceptionWhenFrequencyIsNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new FrequencyGenerator([
            [10, 42],
            [-10, 21],
        ]);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\FrequencyGenerator::shrink
     *
     * @uses Eris\Generator\FrequencyGenerator::__construct
     * @uses Eris\Generator\FrequencyGenerator::__invoke
     */
    public function shrinking(): void
    {
        $dut = new FrequencyGenerator([
            [10, 42],
            [1, 21],
        ]);

        for ($i = 0; $i < $this->size; $i++) {
            $generatedValue = $dut($this->size, $this->rand);
            $shrunkValue    = $dut->shrink($generatedValue)->last()->value();

            $this->assertThat(
                $shrunkValue,
                $this->logicalOr(
                    $this->equalTo(42),
                    $this->equalTo(21)
                )
            );
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\FrequencyGenerator::shrink
     *
     * @uses Eris\Generator\FrequencyGenerator::__construct
     * @uses Eris\Generator\FrequencyGenerator::__invoke
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function shrinkIntersectingDomainsOnlyShrinkInTheDomainThatOriginallyProducedTheValue(): void
    {
        $dut = new FrequencyGenerator([
            [5, new ChooseGenerator(1, 100)],
            [3, new ChooseGenerator(10, 100)],
        ]);

        $shrinkedTable = [];
        for ($i = 0; $i < 100; $i++) {
            $element = $dut($this->size, $this->rand);

            for ($j = 0; $j < 100; $j++) {
                $element = $dut->shrink($element)->last();
            }
            $shrinkedTable[$element->value()] = true;
        }

        $this->assertEquals([1 => true, 10 => true], $shrinkedTable);
    }

    /**
     * @return array<int>
     */
    private function distribute(FrequencyGenerator $dut): array
    {
        $countOf = [];
        for ($i = 0; $i < 1000; $i++) {
            $value = (int) $dut($this->size, $this->rand)->value();

            $countOf[$value] = isset($countOf[$value]) ? $countOf[$value] + 1 : 1;
        }

        return $countOf;
    }
}
