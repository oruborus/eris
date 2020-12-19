<?php

namespace Eris\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use PHPUnit\Framework\TestCase;

class CharacterGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->size = 0;
        $this->rand = new RandomRange(new RandSource());
    }

    public function testBasicAsciiCharacterGenerators()
    {
        $generator = CharacterGenerator::ascii();
        for ($i = 0; $i < 100; $i++) {
            $value = $generator($this->size, $this->rand);
            $value = $value->unbox();
            $this->assertEquals(1, strlen($value));
            $this->assertGreaterThanOrEqual(0, ord($value));
            $this->assertLessThanOrEqual(127, ord($value));
        }
    }

    public function testPrintableAsciiCharacterGenerators()
    {
        $generator = CharacterGenerator::printableAscii();
        for ($i = 0; $i < 100; $i++) {
            $value = $generator($this->size, $this->rand);
            $value = $value->unbox();
            $this->assertEquals(1, strlen($value));
            $this->assertGreaterThanOrEqual(32, ord($value));
            $this->assertLessThanOrEqual(127, ord($value));
        }
    }

    public function testCharacterGeneratorsShrinkByConventionToTheLowestCodePoint()
    {
        $generator = CharacterGenerator::ascii();
        $this->assertEquals('@', $generator->shrink(new Value('A'))->unbox());
    }

    public function testTheLowestCodePointCannotBeShrunk()
    {
        $generator = new CharacterGenerator(65, 90);
        $lowest = new Value('A');
        $this->assertEquals(new ValueCollection([$lowest]), $generator->shrink($lowest));
    }
}
