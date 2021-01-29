<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Contracts\Generator;
use Eris\Generator\MapGenerator;
use Eris\Value\Value;

use function Eris\Generator\map;

/**
 * @covers Eris\Generator\map
 *
 * @uses Eris\Generator\MapGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class MapFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAMapGenerator(): void
    {
        $generator = $this->getMockForAbstractClass(Generator::class);
        $generator->method('__invoke')->willReturn(new Value('%VALUE%'));

        $dut = map(static fn (string $value): string => "Mapped value: {$value}", $generator);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(MapGenerator::class, $dut);
        $this->assertSame('Mapped value: %VALUE%', $actual);
    }
}
