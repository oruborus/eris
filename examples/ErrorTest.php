<?php

use Eris\Generator;
use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
    use Eris\TestTrait;

    public function testGenericExceptionsDoNotShrinkButStillShowTheInput()
    {
        $this->forAll(
            Generator\string()
        )
            ->then(function ($string) {
                throw new RuntimeException("Something like a missing array index happened.");
            });
    }
}
