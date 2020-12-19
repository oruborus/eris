<?php

namespace Eris\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use DateTime;
use Eris\Value\Value;
use PHPUnit\Framework\TestCase;

class DateGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->size = 10;
        $this->rand = new RandomRange(new RandSource());
    }

    public function testGenerateDateTimeObjectsInTheGivenInterval()
    {
        $generator = new DateGenerator(
            new DateTime("2014-01-01T00:00:00"),
            new DateTime("2014-01-02T23:59:59")
        );
        $value = $generator($this->size, $this->rand);
        $this->assertInstanceOf('DateTime', $value->unbox());
    }

    public function testDateTimeShrinkGeometrically()
    {
        $generator = new DateGenerator(
            new DateTime("2014-01-01T00:00:00"),
            new DateTime("2014-01-02T23:59:59")
        );
        $this->assertEquals(
            new DateTime("2014-01-01T16:00:00"),
            $generator->shrink(new Value(new DateTime("2014-01-02T08:00:00")))->unbox()
        );
    }

    public function testTheLowerLimitIsTheFixedPointOfShrinking()
    {
        $generator = new DateGenerator(
            $lowerLimit = new DateTime("2014-01-01T00:00:00"),
            new DateTime("2014-01-02T23:59:59")
        );
        $value = new Value(new DateTime("2014-01-01T00:01:00"));
        for ($i = 0; $i < 10; $i++) {
            $value = $generator->shrink($value)->last();
        }
        $this->assertEquals(
            $lowerLimit,
            $value->unbox()
        );
    }
}
