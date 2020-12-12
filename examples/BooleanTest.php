<?php

use Eris\Generator;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

class BooleanTest extends TestCase
{
    use TestTrait;

    public function testBooleanValueIsTrueOrFalse()
    {
        $this->forAll(
            Generator\bool()
        )
            ->then(function ($boolValue) {
                $this->assertTrue(
                    ($boolValue === true || $boolValue === false),
                    "$boolValue is not true nor false"
                );
            });
    }
}
