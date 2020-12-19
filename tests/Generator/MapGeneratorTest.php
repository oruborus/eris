<?php

namespace Eris\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use PHPUnit\Framework\TestCase;

class MapGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->size = 10;
        $this->rand = new RandomRange(new RandSource());
    }

    public function testGeneratesAValueObject()
    {
        $generator = new MapGenerator(
            function ($n) {
                return $n * 2;
            },
            ConstantGenerator::box(1)
        );
        $this->assertEquals(
            2,
            $generator->__invoke($this->size, $this->rand)->unbox()
        );
    }

    public function testShrinksTheOriginalInput()
    {
        $generator = new MapGenerator(
            function ($n) {
                return $n * 2;
            },
            new ChooseGenerator(1, 100)
        );
        $element = $generator->__invoke($this->size, $this->rand);
        $elementAfterShrink = $generator->shrink($element);

        $actual = $elementAfterShrink->unbox();
        $expected = $element->unbox();

        $this->assertLessThanOrEqual($expected, $actual, "Element should have diminished in size");
    }
}
