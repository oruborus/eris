<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\int;

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
