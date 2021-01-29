<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\BooleanGenerator;

use function Eris\Generator\bool;

/**
 * @covers Eris\Generator\bool
 *
 * @uses Eris\Generator\BooleanGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class BoolFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsABooleanGenerator(): void
    {
        $dut = bool();

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(BooleanGenerator::class, $dut);
        $this->assertThat($actual, $this->logicalOr(
            $this->identicalTo(true),
            $this->identicalTo(false)
        ));
    }
}
