<?php

namespace Eris;

use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
    use TestTrait;

    public function testWithGeneratorSize()
    {
        $times         = 100;
        $generatorSize = 100;
        $generator     = Generator\suchThat(function ($n) {
            return $n > 10;
        }, Generator\nat());
        $sample        = $this->sample($generator, $times, $generatorSize);
        $this->assertNotEmpty(count($sample->collected()));
    }
}
