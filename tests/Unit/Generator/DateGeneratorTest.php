<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use DateTime;
use Eris\Generator\DateGenerator;
use Eris\Value\Value;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class DateGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\DateGenerator::__construct
     * @covers Eris\Generator\DateGenerator::__invoke
     */
    public function generateDateTimeObjectsInTheGivenInterval(): void
    {
        $dut = new DateGenerator(
            new DateTime("2014-01-01T00:00:00"),
            new DateTime("2014-01-02T23:59:59")
        );
        $value = $dut($this->size, $this->rand);

        $this->assertInstanceOf(DateTime::class, $value->value());
    }

    /**
     * @test
     *
     * @covers Eris\Generator\DateGenerator::shrink
     *
     * @uses Eris\Generator\DateGenerator::__construct
     */
    public function dateTimeShrinkGeometrically(): void
    {
        $dut = new DateGenerator(
            new DateTime("2014-01-01T00:00:00"),
            new DateTime("2014-01-02T23:59:59")
        );

        $actual = $dut->shrink(new Value(new DateTime("2014-01-02T08:00:00")))->last()->value();

        $this->assertEquals(new DateTime("2014-01-01T16:00:00"), $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\DateGenerator::shrink
     *
     * @uses Eris\Generator\DateGenerator::__construct
     */
    public function theLowerLimitIsTheFixedPointOfShrinking(): void
    {
        $dut = new DateGenerator(
            $lowerLimit = new DateTime("2014-01-01T00:00:00"),
            new DateTime("2014-01-02T23:59:59")
        );

        $actual = new Value(new DateTime("2014-01-01T00:01:00"));

        for ($i = 0; $i < 10; $i++) {
            $actual = $dut->shrink($actual)->last();
        }

        $this->assertEquals($lowerLimit, $actual->value());
    }
}
