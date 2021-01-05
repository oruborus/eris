<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\neg;
use function Eris\Generator\oneOf;
use function Eris\Generator\pos;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class OneOfTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function positiveOrNegativeNumberButNotZero(): void
    {
        $this
            ->forAll(
                oneOf(
                    pos(),
                    neg()
                )
            )
            ->then(function (int $number): void {
                $this->assertNotSame(0, $number);
            });
    }
}
