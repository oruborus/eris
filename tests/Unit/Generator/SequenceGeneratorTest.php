<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ChooseGenerator;
use Eris\Generator\SequenceGenerator;

use function array_sum;
use function count;

/**
 * @uses Eris\cartesianProduct
 * @uses Eris\Generator\AssociativeArrayGenerator
 * @uses Eris\Generator\boxAll
 * @uses Eris\Generator\TupleGenerator
 * @uses Eris\Generator\VectorGenerator
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SequenceGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\SequenceGenerator::__construct
     * @covers Eris\Generator\SequenceGenerator::__invoke
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function respectsGenerationSize(): void
    {
        $dut = new SequenceGenerator(
            new ChooseGenerator(10, 100)
        );
        $countLessThanSize = 0;
        $countEqualToSize = 0;

        for ($size = 0; $size < 400; $size++) {
            $sequenceSize = count($dut($size, $this->rand)->value());

            if ($sequenceSize < $size) {
                $countLessThanSize++;
            }

            if ($sequenceSize === $size) {
                $countEqualToSize++;
            }
        }

        $this->assertGreaterThan(
            0,
            $countLessThanSize,
            "Sequence generator does not generate sequences less than the size."
        );
        $this->assertSame(
            400,
            ($countLessThanSize + $countEqualToSize),
            "Sequence generator has generated sequences greater than the size."
        );
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SequenceGenerator::shrink
     *
     * @uses Eris\Generator\SequenceGenerator::__construct
     * @uses Eris\Generator\SequenceGenerator::__invoke
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function shrink(): void
    {
        $dut = new SequenceGenerator(
            new ChooseGenerator(10, 100)
        );

        do {
            $elements = $dut($this->size, $this->rand);
            $elementsAfterShrink = $dut->shrink($elements);
        } while ($elementsAfterShrink->count() == 0);

        $this->assertCount(count($elements->input()) - 1, $elementsAfterShrink->first()->value());
        $this->assertLessThanOrEqual(count($elements->value()), count($elementsAfterShrink->last()->value()));
        $this->assertLessThanOrEqual(array_sum($elements->value()), array_sum($elementsAfterShrink->last()->value()));
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SequenceGenerator::__construct
     * @covers Eris\Generator\SequenceGenerator::__invoke
     * @covers Eris\Generator\SequenceGenerator::shrink
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function shrinkEmptySequence(): void
    {
        $dut = new SequenceGenerator(
            new ChooseGenerator(10, 100)
        );

        $elements = $dut($size = 0, $this->rand);

        $this->assertSame(0, count($elements->value()));
        $this->assertSame(0, count($dut->shrink($elements)));
    }

    /**
     * @test
     *
     * @covers Eris\Generator\SequenceGenerator::shrink
     *
     * @uses Eris\Generator\SequenceGenerator::__construct
     * @uses Eris\Generator\SequenceGenerator::__invoke
     *
     * @uses Eris\Generator\ChooseGenerator
     */
    public function shrinkEventuallyEndsUpWithNoOptions(): void
    {
        $dut = new SequenceGenerator(
            new ChooseGenerator(10, 100)
        );
        $numberOfShrinks = 0;

        $value = $dut($this->size, $this->rand);
        $options = $dut->shrink($value);

        while (count($options) > 0) {
            if ($numberOfShrinks++ > 100) {
                $this->fail('Too many shrinks');
            }
            $options = $dut->shrink($options->first());
        }

        $this->assertCount(0, $options);
    }
}
