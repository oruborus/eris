<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\BooleanGenerator;
use Test\Unit\Generator\GeneratorTestCase;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class BooleanGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\BooleanGenerator::__invoke
     */
    public function randomlyPicksTrueOrFalse(): void
    {
        $dut = new BooleanGenerator();

        for ($i = 0; $i < 100; $i++) {
            $value = $dut($_size = 0, $this->rand);

            /**
             * @psalm-suppress RedundantConditionGivenDocblockType
             */
            $this->assertIsBool($value->value());
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\BooleanGenerator::__invoke
     * @covers Eris\Generator\BooleanGenerator::shrink
     */
    public function allwaysShrinksToFalse(): void
    {
        $dut = new BooleanGenerator();

        for ($i = 0; $i < 100; $i++) {
            $value = $dut($_size = 10, $this->rand);

            $this->assertFalse($dut->shrink($value)->last()->value());
        }
    }
}
