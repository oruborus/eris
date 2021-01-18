<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\ChooseGenerator;
use Eris\Generator\OneOfGenerator;
use InvalidArgumentException;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class OneOfGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\OneOfGenerator::__construct
     * @covers Eris\Generator\OneOfGenerator::__invoke
     *
     * @uses Eris\Generator\ensureIsGenerator
     * @uses Eris\Generator\ChooseGenerator
     * @uses Eris\Generator\FrequencyGenerator
     */
    public function constructWithAnArrayOfGenerators(): void
    {
        $dut = new OneOfGenerator([
            new ChooseGenerator(0, 100),
            new ChooseGenerator(0, 100),
        ]);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertIsInt($actual);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\OneOfGenerator::__construct
     * @covers Eris\Generator\OneOfGenerator::__invoke
     *
     * @uses Eris\Generator\ensureIsGenerator
     * @uses Eris\Generator\ConstantGenerator
     * @uses Eris\Generator\FrequencyGenerator
     */
    public function constructWithNonGenerators(): void
    {
        $dut = new OneOfGenerator([42, 42]);

        $actual = $dut($this->size, $this->rand)->value();

        $this->assertSame(42, $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\OneOfGenerator::__construct
     * @covers Eris\Generator\OneOfGenerator::__invoke
     *
     * @uses Eris\Generator\ensureIsGenerator
     * @uses Eris\Generator\ConstantGenerator
     * @uses Eris\Generator\FrequencyGenerator
     */
    public function constructWithNoArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OneOfGenerator([]);
    }
}
