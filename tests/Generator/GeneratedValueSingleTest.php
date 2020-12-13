<?php

declare(strict_types=1);

namespace Eris\Generator;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GeneratedValueSingleTest extends TestCase
{
    private static GeneratedValueSingle $instance;

    public static function setUpBeforeClass(): void
    {
        self::$instance = GeneratedValueSingle::fromJustValue('%VALUE%', '%GENERATOR%');
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::map
     * @uses Eris\Generator\GeneratedValueSingle::__construct
     * @uses Eris\Generator\GeneratedValueSingle::fromJustValue
     * @uses Eris\Generator\GeneratedValueSingle::fromValueAndInput
     */
    public function canBeMappedToDeriveValues(): void
    {
        $initial  = GeneratedValueSingle::fromJustValue(3, 'initial-generator');
        $expected = GeneratedValueSingle::fromValueAndInput(6, $initial, 'derived-generator');

        $actual = $initial->map(fn (int $value): int => 2 * $value, 'derived-generator');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::derivedIn
     * @uses Eris\Generator\GeneratedValueSingle::__construct
     * @uses Eris\Generator\GeneratedValueSingle::fromJustValue
     * @uses Eris\Generator\GeneratedValueSingle::fromValueAndInput
     */
    public function derivedValueCanBeAnnotatedWithNewGeneratorNameWithoutBeingChanged(): void
    {
        $initial  = GeneratedValueSingle::fromJustValue(3, 'initial-generator');
        $expected = GeneratedValueSingle::fromValueAndInput(3, $initial, 'derived-generator');

        $actual = $initial->derivedIn('derived-generator');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::__toString
     * @uses Eris\Generator\GeneratedValueSingle::__construct
     * @uses Eris\Generator\GeneratedValueSingle::fromJustValue
     * @uses Eris\Generator\GeneratedValueSingle::fromValueAndInput
     */
    public function canBeRepresentedOnOutput(): void
    {
        $initial = GeneratedValueSingle::fromValueAndInput(422, GeneratedValueSingle::fromJustValue(211), 'map');

        $actual = (string) $initial;

        $this->assertMatchesRegularExpression('/value.*422/', $actual);
        $this->assertMatchesRegularExpression('/211/', $actual);
        $this->assertMatchesRegularExpression('/generator.*map/', $actual);
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::getIterator
     * @uses Eris\Generator\GeneratedValueSingle::__construct
     * @uses Eris\Generator\GeneratedValueSingle::fromJustValue
     * @uses Eris\Generator\GeneratedValueSingle::fromValueAndInput
     */
    public function canBeIteratedUponAsASingleOption(): void
    {
        $initial = GeneratedValueSingle::fromValueAndInput(42, GeneratedValueSingle::fromJustValue(21), '%GENERATOR%');
        $expected = [$initial];

        $actual = \iterator_to_array($initial);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::count
     * @uses Eris\Generator\GeneratedValueSingle::__construct
     * @uses Eris\Generator\GeneratedValueSingle::fromJustValue
     * @uses Eris\Generator\GeneratedValueSingle::fromValueAndInput
     */
    public function canBeCounted(): void
    {
        $initial = GeneratedValueSingle::fromValueAndInput(42, GeneratedValueSingle::fromJustValue(21), '%GENERATOR%');

        $this->assertCount(1, $initial);
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::input
     * @uses Eris\Generator\GeneratedValueSingle::__construct
     * @uses Eris\Generator\GeneratedValueSingle::fromJustValue
     * @uses Eris\Generator\GeneratedValueSingle::fromValueAndInput
     */
    public function returnsValueForInputWhenConstructedWithoutInput(): void
    {
        $expected = 211;
        $initial  = GeneratedValueSingle::fromJustValue($expected, '%GENERATOR%');

        $actual = $initial->input();

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::input
     * @uses Eris\Generator\GeneratedValueSingle::__construct
     * @uses Eris\Generator\GeneratedValueSingle::fromJustValue
     * @uses Eris\Generator\GeneratedValueSingle::fromValueAndInput
     */
    public function returnsInputWhenConstructedWithInput(): void
    {
        $expected = 211;
        $initial  = GeneratedValueSingle::fromValueAndInput('%VALUE%', $expected, '%GENERATOR%');

        $actual = $initial->input();

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::unbox
     * @uses Eris\Generator\GeneratedValueSingle::__construct
     * @uses Eris\Generator\GeneratedValueSingle::fromJustValue
     * @uses Eris\Generator\GeneratedValueSingle::fromValueAndInput
     *
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    public function returnsValue(): void
    {
        $expected = 211;
        $initial1  = GeneratedValueSingle::fromJustValue($expected, '%GENERATOR%');
        $initial2  = GeneratedValueSingle::fromValueAndInput($expected, '%INPUT%', '%GENERATOR%');

        $actual1 = $initial1->unbox();
        $actual2 = $initial2->unbox();

        $this->assertSame($expected, $actual1);
        $this->assertSame($expected, $actual2);
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::generatorName
     * @uses Eris\Generator\GeneratedValueSingle::__construct
     * @uses Eris\Generator\GeneratedValueSingle::fromJustValue
     * @uses Eris\Generator\GeneratedValueSingle::fromValueAndInput
     */
    public function returnsGeneratorName(): void
    {
        $expected = 'initial-generator';
        $initial1  = GeneratedValueSingle::fromJustValue('%VALUE%', $expected);
        $initial2  = GeneratedValueSingle::fromValueAndInput('%VALUE%', '%INPUT%', $expected);

        $actual1 = $initial1->generatorName();
        $actual2 = $initial2->generatorName();

        $this->assertSame($expected, $actual1);
        $this->assertSame($expected, $actual2);
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::merge
     * @uses Eris\Generator\GeneratedValueSingle::__construct
     * @uses Eris\Generator\GeneratedValueSingle::fromJustValue
     * @uses Eris\Generator\GeneratedValueSingle::fromValueAndInput
     */
    public function throwsExceptionWhenMergedWithInstanceFromDifferentGenerator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to merge a initial-generator GeneratedValueSingle with a not-initial-generator GeneratedValueSingle');

        $merge =
            /**
             * @param mixed $first
             * @param mixed $second
             * @return mixed
             */
            fn ($first, $second) => $first;
        $initial1 = GeneratedValueSingle::fromJustValue('%VALUE%', 'initial-generator');
        $initial2 = GeneratedValueSingle::fromJustValue('%VALUE%', 'not-initial-generator');

        $initial1->merge($initial2, $merge);
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::merge
     * @uses Eris\Generator\GeneratedValueSingle::__construct
     * @uses Eris\Generator\GeneratedValueSingle::fromJustValue
     * @uses Eris\Generator\GeneratedValueSingle::fromValueAndInput
     */
    public function mergesWithInstanceFromSameGenerator(): void
    {
        $merge =
            /**
             * @param mixed $first
             * @param mixed $second
             * @return mixed
             */
            fn ($first, $second) => $first;
        $initial1 = GeneratedValueSingle::fromJustValue('%VALUE%', 'initial-generator');
        $initial2 = GeneratedValueSingle::fromJustValue('%VALUE%', 'initial-generator');

        $actual = $initial1->merge($initial2, $merge);

        $this->assertEquals($initial1, $actual);
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::add
     * @uses Eris\Generator\GeneratedValueSingle::__construct
     * @uses Eris\Generator\GeneratedValueSingle::fromJustValue
     * @uses Eris\Generator\GeneratedValueSingle::fromValueAndInput
     * @uses Eris\Generator\GeneratedValueOptions
     */
    public function addingAnotherInstanceResultsInCollectionContainingBotInstances(): void
    {
        $initial1 = GeneratedValueSingle::fromJustValue('%VALUE%', '%GENERATOR%');
        $initial2 = GeneratedValueSingle::fromJustValue('%VALUE%', '%GENERATOR%');

        $actual = $initial1->add($initial2);

        $this->assertInstanceOf(GeneratedValueOptions::class, $actual);
        $this->assertCount(2, $actual);
        $this->assertEquals($initial1, $actual->first());
        $this->assertEquals($initial2, $actual->last());
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::__construct
     * @covers Eris\Generator\GeneratedValueSingle::fromJustValue
     */
    public function throwsExceptionWhenTryingToCreateNewInstanceWithInstanceAsValueWithoutInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('It looks like you are trying to build a GeneratedValueSingle whose value is another GeneratedValueSingle. This is almost always an error as values will be passed as-is to properties and GeneratedValueSingle should be hidden from them.');

        GeneratedValueSingle::fromJustValue(self::$instance, '%GENERATOR%');
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::__construct
     * @covers Eris\Generator\GeneratedValueSingle::fromValueAndInput
     */
    public function throwsExceptionWhenTryingToCreateNewInstanceWithInstanceAsValueWithInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('It looks like you are trying to build a GeneratedValueSingle whose value is another GeneratedValueSingle. This is almost always an error as values will be passed as-is to properties and GeneratedValueSingle should be hidden from them.');

        GeneratedValueSingle::fromValueAndInput(self::$instance, '%INPUT%', '%GENERATOR%');
    }

    /**
     * @test
     * @covers Eris\Generator\GeneratedValueSingle::__construct
     * @covers Eris\Generator\GeneratedValueSingle::fromJustValue
     * @covers Eris\Generator\GeneratedValueSingle::fromValueAndInput
     */
    public function createsInstancesFromStaticCreationMethods(): void
    {
        $actual1 = GeneratedValueSingle::fromJustValue('%VALUE%', '%GENERATOR%');
        $actual2 = GeneratedValueSingle::fromValueAndInput('%VALUE%', '%INPUT%', '%GENERATOR%');

        $this->assertInstanceOf(GeneratedValueSingle::class, $actual1);
        $this->assertInstanceOf(GeneratedValueSingle::class, $actual2);
    }
}
