<?php

use Eris\Generator;
use Eris\Antecedent;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\char;
use function Eris\Antecedent\printableCharacter;
use function Eris\Antecedent\printableCharacters;

class CharacterTest extends TestCase
{
    use Eris\TestTrait;

    public function testLengthOfAsciiCharactersInPhp()
    {
        $this->forAll(
            char(['basic-latin'])
        )
            ->then(function ($char) {
                $this->assertLenghtIs1($char);
            });
    }

    public function testLengthOfPrintableAsciiCharacters()
    {
        $this->forAll(
            char(['basic-latin'])
        )
            ->when(printableCharacter())
            ->then(function ($char) {
                $this->assertFalse(ord($char) < 32);
            });
    }

    public function testMultiplePrintableCharacters()
    {
        $this
            ->minimumEvaluationRatio(0.1)
            ->forAll(
                char(['basic-latin']),
                char(['basic-latin'])
            )
            ->when(printableCharacters())
            ->then(function ($first, $second) {
                $this->assertFalse(ord($first) < 32);
                $this->assertFalse(ord($second) < 32);
            });
    }

    /**
     * @eris-ratio 10
     */
    public function testMultiplePrintableCharactersFromAnnotation()
    {
        $this
            ->forAll(
                char(['basic-latin']),
                char(['basic-latin'])
            )
            ->when(printableCharacters())
            ->then(function ($first, $second) {
                $this->assertFalse(ord($first) < 32);
                $this->assertFalse(ord($second) < 32);
            });
    }

    private function assertLenghtIs1($char)
    {
        $length = strlen($char);
        $this->assertEquals(
            1,
            $length,
            "'$char' is too long: $length"
        );
    }
}
