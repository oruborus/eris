<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\associative;
use function Eris\Generator\choose;
use function Eris\Generator\elements;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class AssociativeArrayTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function associativeArraysGeneratedOnStandardKeys(): void
    {
        $this
            ->forAll(
                associative([
                    'letter' => elements('A', 'B', 'C'),
                    'cipher' => choose(0, 9),
                ])
            )
            ->then(function (array $array): void {
                ['letter' => $letter, 'cipher' => $cipher] = $array;

                $this->assertCount(2, $array);
                $this->assertIsString($letter);
                $this->assertIsInt($cipher);
            });
    }
}
