<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Contracts\Generator;
use Eris\Generator\ConstantGenerator;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\box;

/**
 * @covers Eris\Generator\box
 *
 * @uses Eris\Generator\ConstantGenerator
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class BoxFunctionTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider provideNonGeneratorValues
     *
     * @template TValue
     * @param TValue $value
     * @param ConstantGenerator<TValue> $expected
     */
    public function convertsSingleNonGeneratorValueToConstantGenerator($value, ConstantGenerator $expected): void
    {
        $actual = box($value);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array<string, array>
     */
    public function provideNonGeneratorValues(): array
    {
        return [
            'integer value' => [5, new ConstantGenerator(5)],
            'float value'   => [5.0, new ConstantGenerator(5.0)],
            'string value'  => ['five', new ConstantGenerator('five')],
            'array value'   => [[5, 5.0, 'five'], new ConstantGenerator([5, 5.0, 'five'])],
        ];
    }

    /**
     * @test
     */
    public function returnsValueIfItIsAGenerator(): void
    {
        $generator = $this->getMockForAbstractClass(Generator::class);

        $actual = box($generator);

        $this->assertSame($generator, $actual);
    }
}
