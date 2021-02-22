<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\SubsetGenerator;
use Eris\Quantifier\ForAll;
use Eris\Value\Value;

use function array_count_values;
use function count;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SubsetGeneratorTest extends GeneratorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->size = 100;
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SubSetGenerator::__construct
     * @covers Eris\Generator\SubSetGenerator::__invoke
     */
    public function scalesGenerationSizeToTouchAllPossibleSubsets(): void
    {
        $universe = ['a', 'b', 'c', 'd', 'e'];
        $universeSize = count($universe);
        $dut = new SubsetGenerator($universe);

        $subsetSizes = [];
        for ($size = 0; $size < ForAll::DEFAULT_MAX_SIZE; $size++) {
            $subsetSizes[] = count($dut($size, $this->rand)->value());
        }

        $subsetSizeFrequencies = array_count_values($subsetSizes);

        // notice the full universe is very rarely generated
        // hence its presence is not asserted here
        for ($subsetSize = 0; $subsetSize < $universeSize; $subsetSize++) {
            $this->assertGreaterThan(
                0,
                $subsetSizeFrequencies[$subsetSize],
                "There were no subsets generated of size $subsetSize"
            );
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SubSetGenerator::__construct
     * @covers Eris\Generator\SubSetGenerator::__invoke
     */
    public function noRepeatedElementsAreInTheSet(): void
    {
        $universe = ['a', 'b', 'c', 'd', 'e'];
        $dut = new SubsetGenerator($universe);

        for ($size = 0; $size < ForAll::DEFAULT_MAX_SIZE; $size++) {
            $actual = $dut($size, $this->rand)->value();

            $this->assertEqualsCanonicalizing(
                array_unique($actual),
                $actual,
                "There are repeated elements inside a generated value."
            );
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SubSetGenerator::shrink
     *
     * @uses Eris\Generator\SubSetGenerator::__construct
     * @uses Eris\Generator\SubSetGenerator::__invoke
     */
    public function shrinksOnlyInSizeBecauseShrinkingElementsMayCauseCollisions(): void
    {
        $universe = ['a', 'b', 'c', 'd', 'e'];
        $dut = new SubsetGenerator($universe);

        $elements = $dut($this->size, $this->rand);
        $actual = $dut->shrink($elements)->last()->value();

        $this->assertLessThanOrEqual(count($elements->value()), count($actual));
        $this->assertEqualsCanonicalizing(
            array_unique($actual),
            $actual,
            "There are repeated elements inside a generated value."
        );
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SubSetGenerator::shrink
     *
     * @uses Eris\Generator\SubSetGenerator::__construct
     * @uses Eris\Generator\SubSetGenerator::__invoke
     */
    public function shrinkEmptySet(): void
    {
        $universe = ['a', 'b', 'c', 'd', 'e'];
        $dut = new SubsetGenerator($universe);

        $elements = $dut($size = 0, $this->rand);
        $actual = $dut->shrink($elements)->last()->value();

        $this->assertCount(0, $elements->value());
        $this->assertCount(0, $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SubSetGenerator::shrink
     *
     * @uses Eris\Generator\SubSetGenerator::__construct
     */
    public function shrinkingReducesSizeOfSetByOne(): void
    {
        $universe = ['a', 'b', 'c'];
        $value    = new Value($universe);
        $dut      = new SubsetGenerator($universe);

        $actual = $dut->shrink($value)->last()->value();

        $this->assertCount(2, $actual);
    }
}
