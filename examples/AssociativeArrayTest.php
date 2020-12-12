<?php

use Eris\Generator;
use PHPUnit\Framework\TestCase;

class AssociativeArrayTest extends TestCase
{
    use Eris\TestTrait;

    public function testAssociativeArraysGeneratedOnStandardKeys()
    {
        $this->forAll(
            Generator\associative([
                'letter' => Generator\elements("A", "B", "C"),
                'cipher' => Generator\choose(0, 9),
            ])
        )
            ->then(function ($array) {
                $this->assertEquals(2, count($array));
                $letter = $array['letter'];
                $this->assertIsString($letter);
                $cipher = $array['cipher'];
                $this->assertIsInt($cipher);
            });
    }
}
