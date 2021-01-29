<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\IntegerGenerator;

use function Eris\Generator\int;

/**
 * @covers Eris\Generator\int
 *
 * @uses Eris\Generator\IntegerGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class IntFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAnIntegerGenerator(): void
    {
        $dut = int();

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(IntegerGenerator::class, $dut);
        $this->assertThat($actual, $this->logicalOr(
            $this->greaterThanOrEqual($this->size),
            $this->lessThanOrEqual($this->size)
        ));
    }
}
