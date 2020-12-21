<?php

use Eris\Generator;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\constant;
use function Eris\Generator\nat;

class ConstantTest extends TestCase
{
    use Eris\TestTrait;

    public function testUseConstantGeneratorExplicitly()
    {
        $this
            ->forAll(
                nat(),
                constant(2)
            )
            ->then(function ($number, $alwaysTwo) {
                $this->assertTrue(($number * $alwaysTwo % 2) === 0);
            });
    }

    public function testUseConstantGeneratorImplicitly()
    {
        $this
            ->forAll(
                nat(),
                2
            )
            ->then(function ($number, $alwaysTwo) {
                $this->assertTrue(($number * $alwaysTwo % 2) === 0);
            });
    }
}
