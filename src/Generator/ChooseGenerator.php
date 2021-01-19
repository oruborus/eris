<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function abs;
use function max;
use function min;

/**
 * @implements Generator<int>
 */
class ChooseGenerator implements Generator
{
    private int $lowerLimit;
    private int $upperLimit;
    private int $shrinkTarget;

    public function __construct(int $x, int $y)
    {
        $this->lowerLimit   = min($x, $y);
        $this->upperLimit   = max($x, $y);
        $this->shrinkTarget = min(abs($this->lowerLimit), abs($this->upperLimit));
    }

    /**
     * @return Value<int>
     */
    public function __invoke(int $_size, RandomRange $rand): Value
    {
        $value = $rand->rand($this->lowerLimit, $this->upperLimit);

        return new Value($value);
    }

    /**
     * @param Value<int> $element
     * @return ValueCollection<int>
     */
    public function shrink(Value $element): ValueCollection
    {
        /**
         * @var int $input
         */
        $input = $element->input();

        return new ValueCollection([new Value($input + ($this->shrinkTarget <=> $input))]);
    }
}
