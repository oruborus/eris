<?php

use Eris\Generator;
use PHPUnit\Framework\TestCase;

class BindTest extends TestCase
{
    use Eris\TestTrait;

    public function testCreatingABrandNewGeneratorFromAValue()
    {
        $this->forAll(
            Generator\bind(
                Generator\vector(4, Generator\nat()),
                function ($vector) {
                    return Generator\tuple(
                        Generator\elements($vector),
                        Generator\constant($vector)
                    );
                }
            )
        )
            ->then(function ($tuple) {
                list($element, $vector) = $tuple;
                $this->assertContains($element, $vector);
            });
    }

    // TODO: multiple generators means multiple values passed to the
    // outer Generator factory
}
