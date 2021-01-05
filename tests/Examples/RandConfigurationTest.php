<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\int;
use function Eris\Random\purePhpMtRand;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class RandConfigurationTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function usingTheDefaultRandFunction(): void
    {
        $this
            ->withRand('rand')
            ->forAll(
                int()
            )
            ->withMaxSize(1000 * 1000 * 1000)
            ->then($this->isInteger());
    }

    /**
     * @test
     *
     * @eris-method rand
     */
    public function usingTheDefaultRandFunctionFromAnnotation(): void
    {
        $this
            ->forAll(
                int()
            )
            ->withMaxSize(1000 * 1000 * 1000)
            ->then($this->isInteger());
    }

    /**
     * @test
     */
    public function usingTheDefaultMtRandFunction(): void
    {
        $this
            ->withRand('mt_rand')
            ->forAll(
                int()
            )
            ->then($this->isInteger());
    }

    /**
     * @test
     *
     * @eris-method mt_rand
     */
    public function usingTheDefaultMtRandFunctionFromAnnotation(): void
    {
        $this
            ->forAll(
                int()
            )
            ->then($this->isInteger());
    }

    /**
     * @test
     */
    public function usingThePurePhpMtRandFunction(): void
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('MersenneTwister class does not support HHVM');
        }

        $this
            ->withRand(purePhpMtRand())
            ->forAll(
                int()
            )
            ->then($this->isInteger());
    }

    /**
     * @return callable(mixed): void
     */
    private function isInteger()
    {
        return
            /**
             * @param mixed $number
             */
            function ($number): void {
                $this->assertIsInt($number);
            };
    }
}
