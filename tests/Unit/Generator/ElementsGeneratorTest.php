<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ElementsGenerator;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ElementsGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\ElementsGenerator::__invoke
     *
     * @uses Eris\Generator\ElementsGenerator::__construct
     */
    public function generatesOnlyArgumentsInsideTheGivenArray(): void
    {
        $expected = [1, 4, 5, 9];
        $dut = new ElementsGenerator(...$expected);

        for ($i = 0; $i < 1000; $i++) {
            $actual = $dut($this->size, $this->rand)->value();

            $this->assertContains($actual, $expected);
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\ElementsGenerator::shrink
     *
     * @uses Eris\Generator\ElementsGenerator::__construct
     */
    public function aSingleValueCannotShrinkGivenThereIsNoExplicitRelationshipBetweenTheValuesInTheDomain(): void
    {
        $dut = new ElementsGenerator('A', 2, false);
        $singleValue = new Value(2);
        $expected = new ValueCollection([$singleValue]);

        $this->assertEquals($expected, $dut->shrink($singleValue));
    }

    /**
     * @test
     *
     * @covers Eris\Generator\ElementsGenerator::__construct
     * @covers Eris\Generator\ElementsGenerator::fromArray
     */
    public function canBeCreatedUsingCreationMethod(): void
    {
        $array = ['A', 2, false];

        $this->assertEquals(new ElementsGenerator(...$array), ElementsGenerator::fromArray($array));
    }
}
