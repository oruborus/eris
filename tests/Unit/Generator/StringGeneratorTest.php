<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\StringGenerator;
use Eris\Value\Value;

use function mb_strlen;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class StringGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\StringGenerator::__invoke
     */
    public function randomlyPicksLengthAndCharacters(): void
    {
        $dut = new StringGenerator();

        $lengths = [];
        $usedChars = [];

        for ($i = 0; $i < 1000; $i++) {
            $value = $dut($this->size, $this->rand)->value();
            $length = mb_strlen($value);

            $lengths[$length] = $lengths[$length] ?? 0 + 1;

            for ($j = 0; $j < $length; $j++) {
                $char = $value[$j];

                $usedChars[$char] = $usedChars[$char] ?? 0 + 1;
            }

            $this->assertLessThanOrEqual(10, $length);
        }

        $this->assertCount(11, $lengths);
        // only readable characters
        $this->assertCount(126 - 32, $usedChars);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\StringGenerator::__invoke
     */
    public function respectsTheGenerationSize(): void
    {
        $generationSize = 100;
        $dut = new StringGenerator();

        $value = $dut($generationSize, $this->rand)->value();

        $this->assertLessThanOrEqual($generationSize, mb_strlen($value));
    }

    /**
     * @test
     *
     * @covers Eris\Generator\StringGenerator::shrink
     *
     * @uses Eris\Generator\StringGenerator::__invoke
     */
    public function shrinksByChoppingOffChars(): void
    {
        $dut = new StringGenerator();

        $actual = $dut->shrink(new Value('abcdef'))->last()->value();

        $this->assertSame('abcde', $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\StringGenerator::shrink
     *
     * @uses Eris\Generator\StringGenerator::__invoke
     */
    public function cannotShrinkTheEmptyString(): void
    {
        $dut = new StringGenerator();
        $expected = new Value('');

        $actual = $dut->shrink($expected)->last();

        $this->assertEquals($expected, $actual);
    }
}
