<?php

use Eris\Generator;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

class DisableShrinkingTest extends TestCase
{
    use TestTrait;

    /**
     * Shrinking may be avoided when then() is slow or non-deterministic.
     */
    public function testThenIsNotCalledMultipleTime()
    {
        $this->calls = 0;
        $this
            ->forAll(
                Generator\nat()
            )
            ->disableShrinking()
            ->then(function ($number) {
                $this->calls++;
                $this->assertTrue(false, "Total calls: {$this->calls}");
            });
    }
}
