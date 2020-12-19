<?php

namespace Eris\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use PHPUnit\Framework\TestCase;

class BooleanGeneratorTest extends TestCase
{
    public function setUp(): void
    {
        $this->rand = new RandomRange(new RandSource());
    }

    public function testRandomlyPicksTrueOrFalse()
    {
        $generator = new BooleanGenerator();
        for ($i = 0; $i < 10; $i++) {
            $value = $generator($_size = 0, $this->rand);
            $this->assertIsBool($value->unbox());
        }
    }

    public function testShrinksToFalse()
    {
        $generator = new BooleanGenerator();
        for ($i = 0; $i < 10; $i++) {
            $value = $generator($_size = 10, $this->rand);
            $this->assertFalse($generator->shrink($value)->unbox());
        }
    }
}
