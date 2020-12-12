<?php

use Eris\Generator;
use PHPUnit\Framework\TestCase;

class OneOfTest extends TestCase
{
    use Eris\TestTrait;

    public function testPositiveOrNegativeNumberButNotZero()
    {
        $this
            ->forAll(
                Generator\oneOf(
                    Generator\pos(),
                    Generator\neg()
                )
            )
            ->then(function ($number) {
                $this->assertNotEquals(0, $number);
            });
    }
}
