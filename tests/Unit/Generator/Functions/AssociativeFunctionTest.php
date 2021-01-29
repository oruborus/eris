<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Contracts\Generator;
use Eris\Generator\AssociativeArrayGenerator;
use Eris\Value\Value;

use function Eris\Generator\associative;

/**
 * @covers Eris\Generator\associative
 *
 * @uses Eris\Generator\AssociativeArrayGenerator
 * @uses Eris\Generator\boxAll
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class AssociativeFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsAnAssociativeArrayGenerator(): void
    {
        $generator = $this->getMockForAbstractClass(Generator::class);
        $generator->method('__invoke')->willReturn(new Value('%VALUE%'));

        $dut = associative(['%KEY%' => $generator]);
        $actual = $dut($this->size, $this->rand)->value();

        $this->assertInstanceOf(AssociativeArrayGenerator::class, $dut);
        $this->assertEquals(['%KEY%' => '%VALUE%'], $actual);
    }
}
