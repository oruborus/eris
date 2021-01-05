<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\constant;
use function Eris\Generator\nat;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ConstantTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function useConstantGeneratorExplicitly(): void
    {
        $this
            ->forAll(
                nat(),
                constant(2)
            )
            ->then(function (int $number, int $alwaysTwo): void {
                $this->assertSame(0, $number * $alwaysTwo % 2);
            });
    }

    /**
     * @test
     */
    public function useConstantGeneratorImplicitly(): void
    {
        $this
            ->forAll(
                nat(),
                2
            )
            ->then(function (int $number, int $alwaysTwo): void {
                $this->assertSame(0, $number * $alwaysTwo % 2);
            });
    }
}
