<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\RegexGenerator;

use function Eris\Generator\regex;

/**
 * @covers Eris\Generator\regex
 *
 * @uses Eris\Generator\RegexGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class RegexFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsARegexGenerator(): void
    {
        $dut = regex('[a-z]{3}\d{3}');

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(RegexGenerator::class, $dut);
        $this->assertMatchesRegularExpression('/[a-z]{3}\d{3}/', $actual);
    }
}
