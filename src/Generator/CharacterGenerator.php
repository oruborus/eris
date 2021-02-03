<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Progression\ArithmeticProgression;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function chr;
use function ord;

/**
 * @implements Generator<string>
 */
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
        $this->shrinkingProgression = new ArithmeticProgression($this->lowerLimit);
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
        $shrinkedValue = chr($this->shrinkingProgression->next(ord($element->value())));

        return new ValueCollection([new Value($shrinkedValue)]);
    }
}
