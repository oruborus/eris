<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\AssociativeArrayGenerator;
use Eris\Generator\ChooseGenerator;
use Eris\Generator\ElementsGenerator;

use function array_keys;
use function strlen;

/**
 * @uses Eris\cartesianProduct
 * @uses Eris\Generator\ChooseGenerator
 * @uses Eris\Generator\ElementsGenerator
 * @uses Eris\Generator\ensureAreAllGenerators
 * @uses Eris\Generator\ensureIsGenerator
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class AssociativeArrayGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\AssociativeArrayGenerator::__construct
     * @covers Eris\Generator\AssociativeArrayGenerator::__invoke
     */
    public function constructWithAnAssociativeArrayOfGenerators(): void
    {
        $dut = new AssociativeArrayGenerator([
            'letter' => ElementsGenerator::fromArray(['A', 'B', 'C']),
            'cipher' => ElementsGenerator::fromArray([0, 1, 2]),
        ]);

        $generated = $dut($this->size, $this->rand);

        $array = $generated->value();
        ['letter' => $letter, 'cipher' => $cipher] = $array;

        $this->assertCount(2, $array);
        $this->assertIsString($letter);
        $this->assertSame(1, strlen($letter));
        $this->assertIsInt($cipher);
        $this->assertGreaterThanOrEqual(0, $cipher);
        $this->assertLessThanOrEqual(9, $cipher);
    }

    /**
     * @test
     *
     * @covers Eris\Generator\AssociativeArrayGenerator::shrink
     *
     * @covers Eris\Generator\AssociativeArrayGenerator::__construct
     * @covers Eris\Generator\AssociativeArrayGenerator::__invoke
     */
    public function shrinksTheGeneratorsButKeepsAllTheKeysPresent(): void
    {
        $dut = new AssociativeArrayGenerator([
            'former' => new ChooseGenerator(0, 100),
            'latter' => new ChooseGenerator(0, 100),
        ]);

        $value = $dut($this->size, $this->rand);

        for ($i = 0; $i < 100; $i++) {
            $value = $dut->shrink($value)->last();
            $array = $value->value();

            $this->assertCount(2, $array);
            $this->assertSame(['former', 'latter'], array_keys($array));
            $this->assertIsInt($array['former']);
            $this->assertIsInt($array['latter']);
        }
    }
}
