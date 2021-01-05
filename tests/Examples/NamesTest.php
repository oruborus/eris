<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\Generator\NamesGenerator;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\names;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class NamesTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function generatingNames(): void
    {
        $this
            ->forAll(
                names()
            )
            ->then(function (string $name): void {
                $this->assertIsString($name);
            });
    }

    /**
     * @test
     */
    public function samplingShrinkingOfNames(): void
    {
        $generator = NamesGenerator::defaultDataSet();

        $sample = $this->sampleShrink($generator);

        $this->assertIsArray($sample->collected());
    }
}
