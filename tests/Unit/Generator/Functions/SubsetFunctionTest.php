<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\SubsetGenerator;

use function Eris\Generator\subset;

/**
 * @covers Eris\Generator\subset
 *
 * @uses Eris\Generator\SubsetGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SubsetFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsASubsetGenerator(): void
    {
        $dut = subset([]);

        $this->assertInstanceOf(SubsetGenerator::class, $dut);
    }
}
