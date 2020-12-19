<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;
use DateTime;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

/**
 * @param null|string|DateTime $lowerLimit
 * @param null|string|DateTime $upperLimit
 */
function date($lowerLimit = null, $upperLimit = null): DateGenerator
{
    $box =
        /**
         * @param null|string|DateTime $date
         */
        function ($date): ?DateTime {
            if ($date === null) {
                return $date;
            }
            if ($date instanceof DateTime) {
                return $date;
            }
            return new DateTime($date);
        };
    $withDefault = function (?DateTime $value, DateTime $default): DateTime {
        if ($value !== null) {
            return $value;
        }
        return $default;
    };
    return new DateGenerator(
        $withDefault($box($lowerLimit), new DateTime("@0")),
        // uses a maximum which is conservative
        $withDefault($box($upperLimit), new DateTime("@" . (pow(2, 31) - 1)))
    );
}

class DateGenerator implements Generator
{
    private DateTime $lowerLimit;
    private DateTime $upperLimit;
    private int $intervalInSeconds;

    public function __construct(DateTime $lowerLimit, DateTime $upperLimit)
    {
        $this->lowerLimit = $lowerLimit;
        $this->upperLimit = $upperLimit;
        $this->intervalInSeconds = $upperLimit->getTimestamp() - $lowerLimit->getTimestamp();
    }

    /**
     * @return Value<DateTime>
     */
    public function __invoke(int $_size, RandomRange $rand): Value
    {
        $generatedOffset = $rand->rand(0, $this->intervalInSeconds);

        return new Value($this->fromOffset($generatedOffset));
    }

    /**
     * @param Value<DateTime> $element
     * @return ValueCollection<DateTime>
     */
    public function shrink(Value $element): ValueCollection
    {
        $timeOffset = $element->unbox()->getTimestamp() - $this->lowerLimit->getTimestamp();
        $halvedOffset = (int) floor($timeOffset / 2);

        return new ValueCollection([new Value($this->fromOffset($halvedOffset))]);
    }

    private function fromOffset(int $offset): DateTime
    {
        $chosenTimestamp = $this->lowerLimit->getTimestamp() + $offset;
        $element = new DateTime();
        $element->setTimestamp($chosenTimestamp);
        return $element;
    }
}
