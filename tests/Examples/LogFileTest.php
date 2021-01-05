<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\int;
use function Eris\Listener\log;
use function sys_get_temp_dir;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LogFileTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function writingIterationsOnALogFile(): void
    {
        $this
            ->forAll(
                int()
            )
            ->hook(log(sys_get_temp_dir() . '/eris-log-file-test.log'))
            ->then(function (int $number): void {
                $this->assertIsInt($number);
            });
    }

    /**
     * This test will fail as there will certainly be integers which are greater than 42.
     *
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function logOfFailuresAndShrinking(): void
    {
        $this
            ->forAll(
                int()
            )
            ->hook(log(sys_get_temp_dir() . '/eris-log-file-shrinking.log'))
            ->then(function (int $number): void {
                $this->assertLessThanOrEqual(42, $number);
            });
    }
}
