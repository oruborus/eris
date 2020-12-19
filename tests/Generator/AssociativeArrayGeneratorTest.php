<?php

namespace Eris\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use PHPUnit\Framework\TestCase;

class AssociativeArrayGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->letterGenerator = ElementsGenerator::fromArray(['A', 'B', 'C']);
        $this->cipherGenerator = ElementsGenerator::fromArray([0, 1, 2]);
        $this->smallIntegerGenerator = new ChooseGenerator(0, 100);
        $this->size = 10;
        $this->rand = new RandomRange(new RandSource());
    }

    public function testConstructWithAnAssociativeArrayOfGenerators()
    {
        $generator = new AssociativeArrayGenerator([
            'letter' => $this->letterGenerator,
            'cipher' => $this->cipherGenerator,
        ]);

        $generated = $generator($this->size, $this->rand);

        $array = $generated->unbox();
        $this->assertEquals(2, count($array));
        $letter = $array['letter'];
        $this->assertIsString($letter);
        $this->assertEquals(1, strlen($letter));
        $cipher = $array['cipher'];
        $this->assertIsInt($cipher);
        $this->assertGreaterThanOrEqual(0, $cipher);
        $this->assertLessThanOrEqual(9, $cipher);
        $this->assertSame(2, count($generated->unbox()));
    }

    public function testShrinksTheGeneratorsButKeepsAllTheKeysPresent()
    {
        $generator = new AssociativeArrayGenerator([
            'former' => $this->smallIntegerGenerator,
            'latter' => $this->smallIntegerGenerator,
        ]);

        $value = $generator($this->size, $this->rand);

        for ($i = 0; $i < 100; $i++) {
            $value = $generator->shrink($value)->last();
            $array = $value->unbox();
            $this->assertEquals(2, count($array));
            $this->assertEquals(
                ['former', 'latter'],
                array_keys($array)
            );
            $this->assertIsInt($array['former']);
            $this->assertIsInt($array['latter']);
        }
    }
}
