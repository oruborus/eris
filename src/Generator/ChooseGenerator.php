<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use InvalidArgumentException;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

if (!defined('ERIS_PHP_INT_MIN')) {
    define('ERIS_PHP_INT_MIN', ~PHP_INT_MAX);
}

class ChooseGenerator implements Generator
{
    private int $lowerLimit;
    private int $upperLimit;
    private int $shrinkTarget;

    public function __construct(int $x, int $y)
    {
        // $this->checkLimits($x, $y);

        $this->lowerLimit = min($x, $y);
        $this->upperLimit = max($x, $y);
        $this->shrinkTarget = min(
            abs($this->lowerLimit),
            abs($this->upperLimit)
        );
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
        if ($element->input() > $this->shrinkTarget) {
            return new ValueCollection([new Value($element->input() - 1)]);
        }
        if ($element->input() < $this->shrinkTarget) {
            return new ValueCollection([new Value($element->input() + 1)]);
        }

        return new ValueCollection([$element]);
    }

    /**
     * @param mixed $lowerLimit
     * @param mixed $upperLimit
     */
    private function checkLimits($lowerLimit, $upperLimit): void
    {
        // TODO: the problem with the random number generator is still here.
        if ((!is_int($lowerLimit)) || (!is_int($upperLimit))) {
            throw new InvalidArgumentException(
                'lowerLimit (' . var_export($lowerLimit, true) . ') and ' .
                    'upperLimit (' . var_export($upperLimit, true) . ') should ' .
                    'be Integers between ' . ERIS_PHP_INT_MIN . ' and ' . PHP_INT_MAX
            );
        }
    }
}
