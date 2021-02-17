<?php

declare(strict_types=1);

namespace Test\Unit\Contracts;

use Eris\Contracts\Growth;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GrowthTest extends TestCase
{
    private Growth $dut;

    public function setUp(): void
    {
        parent::setUp();

        $this->dut = $this->getMockForAbstractClass(Growth::class, [0, 0]);

        $reflectionClass = new \ReflectionClass($this->dut);
        $property = $reflectionClass->getProperty('values');
        $property->setAccessible(true);
        $property->setValue($this->dut, [1, 2, 3, 4, 5, 6, 7, 8, 9, 0]);
        $property->setAccessible(false);
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Growth::__construct
     */
    public function canBeConstructed(): void
    {
        $this->assertInstanceOf(Growth::class, $this->dut);
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Growth::count
     * @covers Eris\Contracts\Growth::offsetSet
     * @covers Eris\Contracts\Growth::offsetUnset
     *
     * @uses Eris\Contracts\Growth::__construct
     */
    public function offsetsCannotBeSetOrUnset(): void
    {
        $this->assertCount(10, $this->dut);

        $this->dut[] = 5;
        $this->dut[999] = 321;

        $this->assertCount(10, $this->dut);

        unset($this->dut[0]);

        $this->assertCount(10, $this->dut);
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Growth::offsetExists
     *
     * @uses Eris\Contracts\Growth::__construct
     */
    public function canShowIfOffsetExists(): void
    {
        $this->assertTrue(isset($this->dut[0]));
        $this->assertFalse(isset($this->dut[10]));
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Growth::offsetGet
     *
     * @uses Eris\Contracts\Growth::__construct
     * @uses Eris\Contracts\Growth::count
     */
    public function throwExceptionWhenUndefinedOffsetIsRequested(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Undefined Growth key -7/');

        $this->dut[-7];
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Growth::offsetExists
     * @covers Eris\Contracts\Growth::offsetGet
     *
     * @uses Eris\Contracts\Growth::__construct
     * @uses Eris\Contracts\Growth::count
     */
    public function cyclesThroughAvailableSizesWhenTheyAreFinished(): void
    {
        $this->assertIsInt($this->dut[42000]);
    }
}
