<?php

declare(strict_types=1);

namespace Test\Examples;

use DateTime;
use DateTimeZone;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\ExpectationFailedException;

use function abs;
use function Eris\Generator\choose;
use function Eris\Generator\date;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class DateTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function yearOfADate(): void
    {
        $this
            ->forAll(
                date('2014-01-01T00:00:00', '2014-12-31T23:59:59')
            )
            ->then(function (DateTime $date): void {
                $this->assertSame('2014', $date->format('Y'));
            });
    }

    /**
     * @test
     */
    public function defaultValuesForTheInterval(): void
    {
        $this
            ->forAll(
                date()
            )
            ->then(function (DateTime $date): void {
                $this->assertGreaterThanOrEqual(1970, (int) $date->format('Y'));
                $this->assertLessThanOrEqual(2038, (int) $date->format('Y'));
            });
    }

    /**
     * This test fails as the factory method for DateTime does not respect the distance between days.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function factoryMethodRespectsDistanceBetweenDays(): void
    {
        $this
            ->forAll(
                choose(2000, 2020),
                choose(0, 364),
                choose(0, 364)
            )
            ->then(function (int $year, int $day1, int $day2): void {
                $date1 = fromZeroBasedDayOfYear($year, $day1);
                $date2 = fromZeroBasedDayOfYear($year, $day2);

                $this->assertEquals(
                    abs($day1 - $day2) * 86400,
                    abs($date1->getTimestamp() - $date2->getTimestamp()),
                    "Days of the year $year: $day1, $day2" . PHP_EOL
                        . "{$date1->format(DateTime::ISO8601)}, {$date2->format(DateTime::ISO8601)}"
                );
            });
    }
}

/**
 * Device unter test
 */
function fromZeroBasedDayOfYear(int $year, int $day): DateTime
{
    return DateTime::createFromFormat('z Y H i s', "{$day} {$year} 00 00 00", new DateTimeZone("UTC"));
}
