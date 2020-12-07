<?php

use Eris\Generator;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

class ShrinkingTest extends TestCase
{
    use TestTrait;

    public function testShrinkingAString()
    {
        $this->forAll(
            Generator\string()
        )
            ->then(function ($string) {
                var_dump($string);
                $this->assertNotContains('B', \str_split($string));
            });
    }

    public function testShrinkingRespectsAntecedents()
    {
        $this->forAll(
            Generator\choose(0, 20)
        )
            ->when(function ($number) {
                return $number > 10;
            })
            ->then(function ($number) {
                $this->assertTrue($number % 29 == 0, "The number $number is not multiple of 29");
            });
    }
}
