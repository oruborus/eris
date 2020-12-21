<?php

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

/**
 * Generates character in the ASCII 0-127 range.
 *
 * @param array $characterSets  Only supported charset: "basic-latin"
 * @param string $encoding  Only supported encoding: "utf-8"
 * @return Generator\CharacterGenerator
 */
function char(array $characterSets = ['basic-latin'], $encoding = 'utf-8')
{
    return CharacterGenerator::ascii();
}

/**
 * Generates character in the ASCII 32-127 range, excluding non-printable ones
 * or modifiers such as CR, LF and Tab.
 *
 * @return Generator\CharacterGenerator
 */
function charPrintableAscii()
{
    return CharacterGenerator::printableAscii();
}

class CharacterGenerator implements Generator
{
    private int $lowerLimit;
    private int $upperLimit;
    private ArithmeticProgression $shrinkingProgression;

    public static function ascii(): self
    {
        return new self($lowerLimit = 0, $upperLimit = 127);
    }

    public static function printableAscii(): self
    {
        return new self($lowerLimit = 32, $upperLimit = 126);
    }

    public function __construct(int $lowerLimit, int $upperLimit)
    {
        $this->lowerLimit = $lowerLimit;
        $this->upperLimit = $upperLimit;
        $this->shrinkingProgression = ArithmeticProgression::discrete($this->lowerLimit);
    }

    /**
     * @return Value<string>
     */
    public function __invoke(int $_size, RandomRange $rand): Value
    {
        return new Value(chr($rand->rand($this->lowerLimit, $this->upperLimit)));
    }

    /**
     * @param Value<string> $element
     * @return ValueCollection<string>
     */
    public function shrink(Value $element): ValueCollection
    {
        $shrinkedValue = chr($this->shrinkingProgression->next(ord($element->unbox())));

        return new ValueCollection([new Value($shrinkedValue)]);
    }
}
