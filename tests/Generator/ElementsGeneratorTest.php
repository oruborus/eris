<?php

namespace Eris\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use PHPUnit\Framework\TestCase;

class ElementsGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->size = 10;
        $this->rand = new RandomRange(new RandSource());
    }

    public function testGeneratesOnlyArgumentsInsideTheGivenArray()
    {
        $array = [1, 4, 5, 9];
        $generator = ElementsGenerator::fromArray($array);
        $generated = $generator($this->size, $this->rand);
        for ($i = 0; $i < 1000; $i++) {
            $this->assertContains(
                $generated->unbox(),
                $array
            );
        }
    }

    public function testASingleValueCannotShrinkGivenThereIsNoExplicitRelationshipBetweenTheValuesInTheDomain()
    {
        $generator = ElementsGenerator::fromArray(['A', 2, false]);
        $singleValue = new Value(2);
        $expected = new ValueCollection([$singleValue]);
        $this->assertEquals($expected, $generator->shrink($singleValue));
    }
}
