<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\subset;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SubsetTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function subsetsOfASet(): void
    {
        $this
            ->forAll(
                subset([
                    2, 4, 6, 8, 10
                ])
            )
            ->then(function (array $subset): void {
                foreach ($subset as $element) {
                    $this->assertSame(0, $element % 2, "Element {$element} is not even, where did it come from?");
                }
            });
    }
}
