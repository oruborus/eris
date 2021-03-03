<?php

namespace Eris\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use PHPUnit\Framework\TestCase;

class ConstantGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->size = 0;
        $this->rand = new RandomRange(new RandSource());
    }

    public function testPicksAlwaysTheValue()
    {
        $generator = new ConstantGenerator(true);
        for ($i = 0; $i < 50; $i++) {
            $this->assertTrue($generator($this->size, $this->rand)->unbox());
        }
    }

    public function testShrinkAlwaysToTheValue()
    {
        $generator = new ConstantGenerator(true);
        $element = $generator($this->size, $this->rand);
        for ($i = 0; $i < 50; $i++) {
            $this->assertTrue($generator->shrink($element)->unbox());
        }
    }
}
