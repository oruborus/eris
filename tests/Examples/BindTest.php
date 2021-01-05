<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\Generator\TupleGenerator;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\bind;
use function Eris\Generator\constant;
use function Eris\Generator\elements;
use function Eris\Generator\nat;
use function Eris\Generator\tuple;
use function Eris\Generator\vector;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class BindTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function creatingABrandNewGeneratorFromAValue(): void
    {
        $this
            ->forAll(
                bind(
                    vector(4, nat()),
                    function (array $vector): TupleGenerator {
                        return tuple(
                            elements($vector),
                            constant($vector)
                        );
                    }
                )
            )
            ->then(function (array $tuple): void {
                [$element, $vector] = $tuple;

                $this->assertContains($element, $vector);
            });
    }

    // TODO: multiple generators means multiple values passed to the
    // outer Generator factory
}
