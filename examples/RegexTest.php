<?php

use Eris\Generator;
use PHPUnit\Framework\TestCase;

class RegexTest extends TestCase
{
    use Eris\TestTrait;

    /**
     * Note that * and + modifiers are not supported. @see Generator\regex
     */
    public function testStringsMatchingAParticularRegex()
    {
        $this->forAll(
            Generator\regex("[a-z]{10}")
        )
            ->then(function ($string) {
                $this->assertEquals(10, strlen($string));
            });
    }
}
