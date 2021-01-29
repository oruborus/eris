<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ChooseGenerator;

use function Eris\Generator\choose;

/**
 * @covers Eris\Generator\choose
 *
 * @uses Eris\Generator\ChooseGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ChooseFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAChooseGenerator(): void
    {
        $dut = choose(-5, 5);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(ChooseGenerator::class, $dut);
        $this->assertThat($actual, $this->logicalOr(
            $this->greaterThanOrEqual(-5),
            $this->lessThanOrEqual(5)
        ));
    }
}
