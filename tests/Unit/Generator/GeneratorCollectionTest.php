<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Contracts\Generator;
use Eris\Generator\GeneratorCollection;
use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Value\Value;
use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GeneratorCollectionTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\GeneratorCollection::__construct
     * @covers Eris\Generator\GeneratorCollection::add
     * @covers Eris\Generator\GeneratorCollection::__invoke
     *
     * @uses Eris\Random\RandomRange
     * @uses Eris\Random\RandSource
     * @uses Eris\Value\Value
     */
    public function generatorsCanBeAddedAndAllTheirInterfaceMethodCanGetCalled(): void
    {
        $size  = 5;
        $range = new RandomRange(new RandSource);

        $generator1 = $this->getMockForAbstractClass(Generator::class);
        $generator1
            ->expects($this->once())
            ->method('__invoke')
            ->with($size, $range)
            ->willReturn(new Value(5));

        $generator2 = $this->getMockForAbstractClass(Generator::class);
        $generator2
            ->expects($this->once())
            ->method('__invoke')
            ->with($size, $range)
            ->willReturn(new Value(6));


        $dut = new GeneratorCollection([$generator1]);
        $dut->add($generator2);

        $actual = $dut->__invoke($size, $range);

        $this->assertEquals(new Value([5, 6], [new Value(5), new Value(6)]), $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\GeneratorCollection::toArray
     *
     * @uses Eris\Generator\GeneratorCollection::__construct
     */
    public function allGeneratorsCanBeRetrieved(): void
    {
        $generator1 = $this->getMockForAbstractClass(Generator::class);
        $generator2 = $this->getMockForAbstractClass(Generator::class);

        $dut = new GeneratorCollection([$generator1, $generator2]);

        $this->assertSame([$generator1, $generator2], $dut->toArray());
    }

    /**
     * @test
     *
     * @covers Eris\Generator\GeneratorCollection::shrink
     *
     * @uses Eris\Generator\GeneratorCollection::__construct
     *
     * @uses Eris\Value\Value
     */
    public function throwsRuntimeExceptionOnShrinkUse(): void
    {
        $this->expectException(RuntimeException::class);

        $dut = new GeneratorCollection();

        $dut->shrink(new Value(5));
    }
}
