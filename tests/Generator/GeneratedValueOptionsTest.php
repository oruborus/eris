<?php

declare(strict_types=1);

namespace Eris\Generator;

use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GeneratedValueOptionsTest extends TestCase
{
    /**
     * @test
     * @covers Eris\Generator\GeneratedValueOptions::map
     * @uses Eris\Generator\GeneratedValueOptions::__construct
     *
     * @uses Eris\Generator\GeneratedValueSingle
     */
    public function mapsOverAllTheOptions(): void
    {
        $doubleFn     = fn (int $e): int => 2 * $e;
        $initFn       = fn (int $e): GeneratedValueSingle => GeneratedValueSingle::fromJustValue($e, 'single');
        $doubleInitFn = fn (int $e): GeneratedValueSingle => GeneratedValueSingle::fromJustValue($e, 'single')
            ->map($doubleFn, 'double');

        $values   = [41, 42, 43, 44, 45, 46];
        $initial  = new GeneratedValueOptions(\array_map($initFn, $values));
        $expected = new GeneratedValueOptions(\array_map($doubleInitFn, $values));

        $actual = $initial->map($doubleFn, 'double');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueOptions::add
     * @covers Eris\Generator\GeneratedValueOptions::remove
     * @uses Eris\Generator\GeneratedValueOptions::__construct
     *
     * @uses Eris\Generator\GeneratedValueSingle
     */
    public function instancesCanBeAddedAndRemoved(): void
    {
        $initial = new GeneratedValueOptions([
            GeneratedValueSingle::fromJustValue(42),
            GeneratedValueSingle::fromJustValue(43),
            GeneratedValueSingle::fromJustValue(44),
        ]);
        $this->assertEquals(
            new GeneratedValueOptions([
                GeneratedValueSingle::fromJustValue(44),
                GeneratedValueSingle::fromJustValue(45),
                GeneratedValueSingle::fromJustValue(46),
            ]),
            $initial
                ->add(GeneratedValueSingle::fromJustValue(45))
                ->remove(GeneratedValueSingle::fromJustValue(42))
                ->add(GeneratedValueSingle::fromJustValue(46))
                ->remove(GeneratedValueSingle::fromJustValue(43))
        );
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueOptions::count
     * @covers Eris\Generator\GeneratedValueOptions::remove
     * @uses Eris\Generator\GeneratedValueOptions::__construct
     *
     * @uses Eris\Generator\GeneratedValueSingle
     */
    public function canBeCounted(): void
    {
        $this->assertEquals(
            3,
            \count(new GeneratedValueOptions([
                GeneratedValueSingle::fromJustValue(44),
                GeneratedValueSingle::fromJustValue(45),
                GeneratedValueSingle::fromJustValue(46),
            ]))
        );
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueOptions::cartesianProduct
     * @uses Eris\Generator\GeneratedValueOptions::__construct
     * @uses Eris\Generator\GeneratedValueOptions::count
     * @uses Eris\Generator\GeneratedValueOptions::getIterator
     *
     * @uses Eris\Generator\GeneratedValueSingle
     */
    public function cartesianProductWithOtherValues(): void
    {
        $former = new GeneratedValueOptions([
            GeneratedValueSingle::fromJustValue('a'),
            GeneratedValueSingle::fromJustValue('b'),
        ]);

        $latter = new GeneratedValueOptions([
            GeneratedValueSingle::fromJustValue('1'),
            GeneratedValueSingle::fromJustValue('2'),
            GeneratedValueSingle::fromJustValue('3'),
        ]);

        $mergeFn = fn (string $first, string $second): string => $first . $second;

        $actual = $former->cartesianProduct($latter, $mergeFn);

        $this->assertCount(6, $actual);
        foreach ($actual as $value) {
            $this->assertMatchesRegularExpression('/^[ab][123]$/', $value->unbox());
        }
    }
}
