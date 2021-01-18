<?php

declare(strict_types=1);

namespace Eris\Generator;

use DateTime;
use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function floor;

/**
 * @implements Generator<DateTime>
 */
class DateGenerator implements Generator
{
    private int $lowerLimit;

    private int $interval;

    public function __construct(DateTime $lowerLimit, DateTime $upperLimit)
    {
        $this->lowerLimit = $lowerLimit->getTimestamp();
        $this->interval   = $upperLimit->getTimestamp() - $this->lowerLimit;
    }

    /**
     * @return Value<DateTime>
     */
    public function __invoke(int $_size, RandomRange $rand): Value
    {
        $timestamp = $this->lowerLimit + $rand->rand(0, $this->interval);

        return new Value(new DateTime("@{$timestamp}"));
    }

    /**
     * @param Value<DateTime> $element
     * @return ValueCollection<DateTime>
     */
    public function shrink(Value $element): ValueCollection
    {
        $timestamp = (int) floor(($this->lowerLimit + $element->value()->getTimestamp()) / 2);

        return new ValueCollection([new Value(new DateTime("@{$timestamp}"))]);
    }
}
