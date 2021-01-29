<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ChooseGenerator;

use function Eris\Generator\byte;

/**
 * @covers Eris\Generator\byte
 *
 * @uses Eris\Generator\ChooseGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ByteFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAChooseGenerator(): void
    {
        $dut = byte();

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(ChooseGenerator::class, $dut);
        $this->assertThat($actual, $this->logicalOr(
            $this->greaterThanOrEqual(0),
            $this->lessThanOrEqual(255)
        ));
    }
}
