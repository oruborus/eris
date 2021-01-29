<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\StringGenerator;

use function Eris\Generator\string;

/**
 * @covers Eris\Generator\string
 *
 * @uses Eris\Generator\StringGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class StringFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAStringGenerator(): void
    {
        $dut = string();

        $this->assertInstanceOf(StringGenerator::class, $dut);
    }
}
