<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use DateTime;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

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
