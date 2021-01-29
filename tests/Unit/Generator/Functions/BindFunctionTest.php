<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Contracts\Generator;
use Eris\Generator\BindGenerator;
use Eris\Value\Value;

use function Eris\Generator\bind;

/**
 * @covers Eris\Generator\bind
 *
 * @uses Eris\Generator\BindGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class BindFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsABindGenerator(): void
    {
        $generator = $this->getMockForAbstractClass(Generator::class);
        $generator->method('__invoke')->willReturn(new Value('%VALUE%'));

        $dut = bind($generator, function (string $value): Generator {
            $generator = $this->getMockForAbstractClass(Generator::class);
            $generator->method('__invoke')->willReturn(new Value("Generated inside: {$value}"));

            return $generator;
        });

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(BindGenerator::class, $dut);
        $this->assertSame('Generated inside: %VALUE%', $actual);
    }
}
