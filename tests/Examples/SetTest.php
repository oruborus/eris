<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\nat;
use function Eris\Generator\set;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SetTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function setsOfAnotherGeneratorsDomain(): void
    {
        $this
            ->forAll(
                set(nat())
            )
            ->then(function (array $set): void {
                foreach ($set as $element) {
                    $this->assertGreaterThanOrEqual(0, $element);
                }
            });
    }
}
