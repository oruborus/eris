<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;
use DateTime;

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

    public function __invoke(int $_size, RandomRange $rand)
    {
        $generatedOffset = $rand->rand(0, $this->intervalInSeconds);
        return GeneratedValueSingle::fromJustValue(
            $this->fromOffset($generatedOffset),
            'date'
        );
    }

    /**
     * @return GeneratedValueSingle
     */
    public function shrink(GeneratedValue $element)
    {
        $timeOffset = $element->unbox()->getTimestamp() - $this->lowerLimit->getTimestamp();
        $halvedOffset = (int) floor($timeOffset / 2);
        return GeneratedValueSingle::fromJustValue(
            $this->fromOffset($halvedOffset),
            'date'
        );
    }

    /**
     * @param integer $offset  seconds to be added to lower limit
     * @return DateTime
     */
    private function fromOffset($offset)
    {
        $chosenTimestamp = $this->lowerLimit->getTimestamp() + $offset;
        $element = new DateTime();
        $element->setTimestamp($chosenTimestamp);
        return $element;
    }
}
