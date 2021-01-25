<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Contracts\Generator;
use Eris\Generator\ConstantGenerator;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\boxAll;

/**
 * @covers Eris\Generator\boxAll
 *
 * @uses Eris\Generator\ConstantGenerator
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class BoxAllFunctionTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider provideArrayOfPossiblyNonGeneratorValues
     *
     * @template TValue
     * @param array<Generator<mixed>|TValue> $value
     * @param array<Generator<mixed>|ConstantGenerator<TValue>> $expected
     */
    public function convertsArrayOfPossiblyNonGeneratorValuesToArrayOfConstantGenerators(
        array $value,
        array $expected
    ): void {
        $actual = boxAll($value);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array<string, array>
     */
    public function provideArrayOfPossiblyNonGeneratorValues(): array
    {
        return [
            'mixed array of constant and generator values' => [
                [
                    1,
                    $this->getMockForAbstractClass(Generator::class),
                    'one',
                    $this->getMockForAbstractClass(Generator::class),
                ],
                [
                    new ConstantGenerator(1),
                    $this->getMockForAbstractClass(Generator::class),
                    new ConstantGenerator('one'),
                    $this->getMockForAbstractClass(Generator::class),
                ]
            ],
            'mixed array with keys of constant and generator values' => [
                [
                    'one'   => 1,
                    'two'   => $this->getMockForAbstractClass(Generator::class),
                    'three' => 'one',
                    'four'  => $this->getMockForAbstractClass(Generator::class),
                ],
                [
                    'one'   => new ConstantGenerator(1),
                    'two'   => $this->getMockForAbstractClass(Generator::class),
                    'three' => new ConstantGenerator('one'),
                    'four'  => $this->getMockForAbstractClass(Generator::class),
                ]
            ],
        ];
    }
}
