<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\ExpectationFailedException;

use function Eris\Generator\choose;
use function Eris\Generator\frequency;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FrequencyTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function falsyValues(): void
    {
        $this
            ->forAll(
                frequency(
                    [8, false],
                    [4, 0],
                    [4, '']
                )
            )
            ->then(function ($falsyValue): void {
                $this->assertFalse((bool) $falsyValue);
            });
    }

    /**
     * This test fails as no posible option is 0
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function alwaysFails(): void
    {
        $this
            ->forAll(
                frequency(
                    [8, choose(1, 100)],
                    [4, choose(100, 200)],
                    [4, choose(200, 300)]
                )
            )
            ->then(function (int $element): void {
                $this->assertSame(0, $element);
            });
    }
}
