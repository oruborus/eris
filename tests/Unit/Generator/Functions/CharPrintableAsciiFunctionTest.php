<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\CharacterGenerator;

use function Eris\Generator\charPrintableAscii;

/**
 * @covers Eris\Generator\charPrintableAscii
 *
 * @uses Eris\Generator\ArithmeticProgression
 * @uses Eris\Generator\CharacterGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CharPrintableAsciiFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsACharacterGenerator(): void
    {
        $dut = charPrintableAscii();

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(CharacterGenerator::class, $dut);
        $this->assertThat($actual, $this->logicalOr(
            $this->greaterThanOrEqual(32),
            $this->lessThanOrEqual(126)
        ));
    }
}
