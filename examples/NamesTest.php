<?php

use Eris\Generator;

class NamesTest extends PHPUnit_Framework_TestCase
{
    use Eris\TestTrait;

    public function testGeneratingNames()
    {
        $this->forAll(
            Generator\names()
        )->then(function ($name) {
            $this->assertIsString($name);
            var_dump($name);
        });
    }

    public function testSamplingShrinkingOfNames()
    {
        $generator = Generator\NamesGenerator::defaultDataSet();
        $sample = $this->sampleShrink($generator);
        $this->assertIsArray($sample->collected());
        var_dump($sample->collected());
    }
}
