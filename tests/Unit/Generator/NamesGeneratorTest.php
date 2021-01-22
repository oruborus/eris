<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\NamesGenerator;
use Eris\Value\Value;

use function strlen;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class NamesGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\NamesGenerator::__construct
     * @covers Eris\Generator\NamesGenerator::__invoke
     * @covers Eris\Generator\NamesGenerator::defaultDataSet
     */
    public function itRespectsTheGenerationSize(): void
    {
        $dut = NamesGenerator::defaultDataSet();

        for ($i = 5; $i < 50; $i++) {
            $value = $dut($maxLength = $i, $this->rand)->value();

            $this->assertLessThanOrEqual(
                $maxLength,
                strlen($value),
                "Names generator is not respecting the generation size. Asked a name with max size {$maxLength} and returned {$value}"
            );
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\NamesGenerator::__construct
     * @covers Eris\Generator\NamesGenerator::__invoke
     */
    public function returnsEmptyStringWhenSmallesElementOfDatasetIsGreaterThenMaximumSize(): void
    {
        $dut = new NamesGenerator([
            'Sir John McLongname III.',
            'Adelaide Maximowa Müller-Lüdenscheidt',
        ]);

        $actual = $dut(10, $this->rand)->value();

        $this->assertSame('', $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\NamesGenerator::__construct
     * @covers Eris\Generator\NamesGenerator::__invoke
     * @covers Eris\Generator\NamesGenerator::defaultDataSet
     */
    public function generatesANameFromAFixedDataset(): void
    {
        $dut = NamesGenerator::defaultDataSet();

        for ($i = 0; $i < 50; $i++) {
            $value = $dut($_size = 10, $this->rand)->value();

            $this->assertIsString($value);
        }
    }

    /**
     * @test
     *
     * @covers Eris\Generator\NamesGenerator::shrink
     *
     * @uses Eris\Generator\NamesGenerator::__construct
     * @uses Eris\Generator\NamesGenerator::__invoke
     * @uses Eris\Generator\NamesGenerator::defaultDataSet
     *
     * @dataProvider provideNamesToShrink
     */
    public function shrinksToTheNameWithTheImmediatelyLowerLengthWhichHasTheMinimumDistance(string $expected, string $original): void
    {
        $dut = NamesGenerator::defaultDataSet();

        $actual = $dut->shrink(new Value($original))->last()->value();

        $this->assertSame($expected, $actual);
    }

    public function provideNamesToShrink(): array
    {
        return [
            ["Malene", "Maxence"],
            ["Columban", "Columbano"],
            ["Carol-Anne", "Carole-Anne"],
            ["Annie", "Zinnia"],
            ["Aletta", "Lucetta"],
            ["Tekla", "Thekla"],
            ["Ursin", "Ursine"],
            ["Gwennan", "Gwenegan"],
            ["Eliane", "Eliabel"],
            ["Ed", "Ed"],
            ["Di", "Di"],
        ];
    }
}
