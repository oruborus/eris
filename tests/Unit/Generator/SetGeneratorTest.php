<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ChooseGenerator;
use Eris\Generator\ConstantGenerator;
use Eris\Generator\SetGenerator;

use function array_unique;
use function count;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SetGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\SetGenerator::__construct
     * @covers Eris\Generator\SetGenerator::__invoke
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function respectsGenerationSize(): void
    {
        $dut = new SetGenerator(
            new ChooseGenerator(10, 100)
        );

        $countLessThanSize = 0;
        $countEqualToSize = 0;
        for ($size = 0; $size < 400; $size++) {
            $subsetSize = count($dut($size, $this->rand)->value());

            if ($subsetSize < $size) {
                $countLessThanSize++;
            }

            if ($subsetSize === $size) {
                $countEqualToSize++;
            }
        }

        $this->assertGreaterThan(
            0,
            $countLessThanSize,
            "Set generator does not generate subsets less than the size."
        );
        $this->assertSame(
            400,
            ($countLessThanSize + $countEqualToSize),
            "Set generator has generated subsets greater than the size."
        );
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SetGenerator::__construct
     * @covers Eris\Generator\SetGenerator::__invoke
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function noRepeatedElementsAreInTheSet(): void
    {
        $dut = new SetGenerator(
            new ChooseGenerator(10, 100)
        );

        for ($size = 0; $size < 10; $size++) {
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
     * @covers Eris\Generator\SetGenerator::__construct
     * @covers Eris\Generator\SetGenerator::__invoke
     *
     * @uses Eris\Generator\ConstantGenerator
     */
    public function stopsBeforeInfiniteLoopsInTryingToExtractNewElementsToPutInTheSet(): void
    {
        $dut = new SetGenerator(
            new ConstantGenerator(42)
        );

        for ($size = 0; $size < 5; $size++) {
            $actual = $dut($size, $this->rand)->value();

            $this->assertLessThanOrEqual(1, count($actual));
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SetGenerator::shrink
     *
     * @uses Eris\Generator\SetGenerator::__construct
     * @uses Eris\Generator\SetGenerator::__invoke
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function shrinksOnlyInSizeBecauseShrinkingElementsMayCauseCollisions(): void
    {
        $dut = new SetGenerator(
            new ChooseGenerator(10, 100)
        );

        $elements = $dut($this->size, $this->rand);
        $actual  =  $dut->shrink($elements)->last()->value();

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
     * @covers Eris\Generator\SetGenerator::shrink
     *
     * @uses Eris\Generator\SetGenerator::__construct
     * @uses Eris\Generator\SetGenerator::__invoke
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function shrinkEmptySet(): void
    {
        $dut = new SetGenerator(
            new ChooseGenerator(10, 100)
        );

        $elements = $dut($size = 0, $this->rand);
        $actual = $dut->shrink($elements)->last()->value();

        $this->assertEquals(0, count($elements->value()));
        $this->assertEquals(0, count($actual));
    }
}
