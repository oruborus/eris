<?php

declare(strict_types=1);

namespace Test\Unit\Random;

use Eris\Random\RandSource;
use PHPUnit\Framework\TestCase;

use function crc32;
use function getrandmax;
use function microtime;
use function rand;
use function srand;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class RandSourceTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Random\RandSource
     */
    public function wrapsCoreRandFunction(): void
    {
        $seed = crc32(microtime());

        srand($seed);
        $expected = rand(0, getrandmax());

        $dut = new RandSource();

        $actual = $dut->seed($seed)->extractNumber();

        $this->assertSame($expected, $actual);
    }
}
