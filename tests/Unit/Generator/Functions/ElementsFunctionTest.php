<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use DateTime;
use Eris\Generator\ElementsGenerator;

use function Eris\Generator\elements;

/**
 * @covers Eris\Generator\elements
 *
 * @uses Eris\Generator\ElementsGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ElementsFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAnElementsGeneratorWithVariadicArguments(): void
    {
        $dut = elements(1, 2, 3);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(ElementsGenerator::class, $dut);
        $this->assertThat($actual, $this->logicalOr(
            $this->identicalTo(1),
            $this->identicalTo(2),
            $this->identicalTo(3),
        ));
    }

    /**
     * @test
     */
    public function createsAnElementsGeneratorWithOneArrayAsSingularAgument(): void
    {
        $dut = elements([1, 2, 3]);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(ElementsGenerator::class, $dut);
        $this->assertThat($actual, $this->logicalOr(
            $this->identicalTo(1),
            $this->identicalTo(2),
            $this->identicalTo(3),
        ));
    }

    /**
     * @test
     */
    public function createsAnElementsGeneratorArraysAsFirstArgument(): void
    {
        $dut = elements([1], 2, 3);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(ElementsGenerator::class, $dut);
        $this->assertThat($actual, $this->logicalOr(
            $this->identicalTo([1]),
            $this->identicalTo(2),
            $this->identicalTo(3),
        ));
    }
}
