<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\CharacterGenerator;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function ord;
use function strlen;

/**
 * @uses Eris\Progression\ArithmeticProgression
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CharacterGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\CharacterGenerator::__construct
     * @covers Eris\Generator\CharacterGenerator::__invoke
     * @covers Eris\Generator\CharacterGenerator::ascii
     */
    public function basicAsciiCharacterGenerators(): void
    {
        $dut = CharacterGenerator::ascii();

        for ($i = 0; $i < 100; $i++) {
            $value = $dut($this->size, $this->rand)->value();

            $this->assertEquals(1, strlen($value));
            $this->assertGreaterThanOrEqual(0, ord($value));
            $this->assertLessThanOrEqual(127, ord($value));
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\CharacterGenerator::__construct
     * @covers Eris\Generator\CharacterGenerator::__invoke
     * @covers Eris\Generator\CharacterGenerator::printableAscii
     */
    public function printableAsciiCharacterGenerators(): void
    {
        $dut = CharacterGenerator::printableAscii();

        for ($i = 0; $i < 250; $i++) {
            $value = $dut($this->size, $this->rand)->value();

            $this->assertEquals(1, strlen($value));
            $this->assertGreaterThanOrEqual(32, ord($value));
            $this->assertLessThanOrEqual(126, ord($value));
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\CharacterGenerator::shrink
     *
     * @uses Eris\Generator\CharacterGenerator::__construct
     * @uses Eris\Generator\CharacterGenerator::ascii
     */
    public function characterGeneratorsShrinkByConventionToTheLowestCodePoint(): void
    {
        $dut = CharacterGenerator::ascii();

        $actual = $dut->shrink(new Value('A'))->last()->value();

        $this->assertEquals('@', $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\CharacterGenerator::shrink
     *
     * @uses Eris\Generator\CharacterGenerator::__construct
     */
    public function theLowestCodePointCannotBeShrunk(): void
    {
        $lowest = new Value('A');
        $expected = new ValueCollection([$lowest]);
        $dut = new CharacterGenerator(65, 90);

        $actual = $dut->shrink($lowest);

        $this->assertEquals($expected, $actual);
    }
}
