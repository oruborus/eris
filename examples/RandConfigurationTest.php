<?php

use Eris\Generator;
use Eris\Random;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

class RandConfigurationTest extends TestCase
{
    use TestTrait;

    public function testUsingTheDefaultRandFunction()
    {
        $this
            ->withRand('rand')
            ->forAll(
                Generator\int()
            )
            ->withMaxSize(1000 * 1000 * 1000)
            ->then($this->isInteger());
    }

    /**
     * @eris-method rand
     */
    public function testUsingTheDefaultRandFunctionFromAnnotation()
    {
        $this
            ->forAll(
                Generator\int()
            )
            ->withMaxSize(1000 * 1000 * 1000)
            ->then($this->isInteger());
    }

    public function testUsingTheDefaultMtRandFunction()
    {
        $this
            ->withRand('mt_rand')
            ->forAll(
                Generator\int()
            )
            ->then($this->isInteger());
    }


    /**
     * @eris-method mt_rand
     */
    public function testUsingTheDefaultMtRandFunctionFromAnnotation()
    {
        $this
            ->forAll(
                Generator\int()
            )
            ->then($this->isInteger());
    }

    public function testUsingThePurePhpMtRandFunction()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('MersenneTwister class does not support HHVM');
        }

        $this
            ->withRand(Random\purePhpMtRand())
            ->forAll(
                Generator\int()
            )
            ->then($this->isInteger());
    }

    private function isInteger()
    {
        return function ($number) {
            $this->assertIsInt($number);
        };
    }
}
