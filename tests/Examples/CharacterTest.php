<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Antecedent\arePrintableCharacters;
use function Eris\Antecedent\isPrintableCharacter;
use function Eris\Generator\char;
use function strlen;
use function ord;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CharacterTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function lengthOfAsciiCharactersInPhp(): void
    {
        $this
            ->forAll(
                char(['basic-latin'])
            )
            ->then(function (string $char): void {
                $this->assertSame(1, strlen($char), "'{$char}' is too long");
            });
    }

    /**
     * @test
     */
    public function lengthOfPrintableAsciiCharacters(): void
    {
        $this
            ->forAll(
                char(['basic-latin'])
            )
            ->when(isPrintableCharacter())
            ->then(function (string $char): void {
                $this->assertGreaterThanOrEqual(32, ord($char));
            });
    }

    /**
     * @test
     */
    public function multiplePrintableCharacters(): void
    {
        $this
            ->minimumEvaluationRatio(0.1)
            ->forAll(
                char(['basic-latin']),
                char(['basic-latin'])
            )
            ->when(arePrintableCharacters())
            ->then(function (string $first, string $second): void {
                $this->assertGreaterThanOrEqual(32, ord($first));
                $this->assertGreaterThanOrEqual(32, ord($second));
            });
    }

    /**
     * @test
     *
     * @eris-ratio 10
     */
    public function multiplePrintableCharactersFromAnnotation(): void
    {
        $this
            ->forAll(
                char(['basic-latin']),
                char(['basic-latin'])
            )
            ->when(arePrintableCharacters())
            ->then(function (string $first, string $second): void {
                $this->assertGreaterThanOrEqual(32, ord($first));
                $this->assertGreaterThanOrEqual(32, ord($second));
            });
    }
}
