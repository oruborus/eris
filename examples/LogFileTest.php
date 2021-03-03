<?php

use Eris\Generator;
use Eris\TestTrait;
use Eris\Listener;

class LogFileTest extends PHPUnit_Framework_TestCase
{
    use TestTrait;

    public function testWritingIterationsOnALogFile()
    {
        $this
            ->forAll(
                Generator\int()
            )
            ->hook(Listener\log(sys_get_temp_dir() . '/eris-log-file-test.log'))
            ->then(function ($number) {
                $this->assertIsInt($number);
            });
    }

    public function testLogOfFailuresAndShrinking()
    {
        $this
            ->forAll(
                Generator\int()
            )
            ->hook(Listener\log(sys_get_temp_dir() . '/eris-log-file-shrinking.log'))
            ->then(function ($number) {
                $this->assertLessThanOrEqual(42, $number);
            });
    }
}
