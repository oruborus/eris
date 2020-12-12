<?php

use Eris\Generator;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

class SetTest extends TestCase
{
    use TestTrait;

    public function testSetsOfAnotherGeneratorsDomain()
    {
        $this->forAll(
            Generator\set(Generator\nat())
        )
            ->then(function ($set) {
                $this->assertIsArray($set);
                foreach ($set as $element) {
                    $this->assertGreaterThanOrEqual(0, $element);
                }
            });
    }
}
