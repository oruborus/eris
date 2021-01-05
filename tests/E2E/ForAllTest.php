<?php

declare(strict_types=1);

namespace Test\E2E;

use PHPUnit\Framework\ExpectationFailedException;
use Test\Support\EndToEndTestCase;
use PHPUnit\Framework\TestCase;

use Test\Examples\AssociativeArrayTest;
use Test\Examples\BindTest;
use Test\Examples\BooleanTest;
use Test\Examples\CharacterTest;
use Test\Examples\ChooseTest;
use Test\Examples\CollectTest;
use Test\Examples\ConstantTest;
use Test\Examples\DateTest;
use Test\Examples\DifferentElementsTest;
use Test\Examples\ElementsTest;
use Test\Examples\FloatTest;
use Test\Examples\FrequencyTest;
use Test\Examples\GeneratorSamplesTest;
use Test\Examples\IntegerTest;
use Test\Examples\LimitToTest;
use Test\Examples\MapTest;
use Test\Examples\NamesTest;
use Test\Examples\OneOfTest;
use Test\Examples\RandConfigurationTest;
use Test\Examples\ReadmeTest;
use Test\Examples\RegexTest;
use Test\Examples\SequenceTest;
use Test\Examples\SetTest;
use Test\Examples\SortTest;
use Test\Examples\SubsetTest;
use Test\Examples\SuchThatTest;
use Test\Examples\SumTest;
use Test\Examples\TupleTest;
use Test\Examples\VectorTest;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ForAllTest extends EndToEndTestCase
{
    /**
     * @test
     *
     * @dataProvider provideTestCases
     *
     * @param class-string<TestCase> $testCase
     */
    public function aTestCaseCanBeRunSuccessfully(string $testCase): void
    {
        $result = $this->runTestClass($testCase);

        $result->assertWasSuccessful();
    }

    public function provideTestCases(): array
    {
        return [
            'AssociativeArrayTest'  => [AssociativeArrayTest::class],
            'BooleanTest'           => [BooleanTest::class],
            'BindTest'              => [BindTest::class],
            'CharacterTest'         => [CharacterTest::class],
            'ChooseTest'            => [ChooseTest::class],
            'CollectTest'           => [CollectTest::class],
            'ConstantTest'          => [ConstantTest::class],
            'DifferentElementsTest' => [DifferentElementsTest::class],
            'ElementsTest'          => [ElementsTest::class],
            'GeneratorSamplesTest'  => [GeneratorSamplesTest::class],
            'IntegerTest'           => [IntegerTest::class],
            'LimitToTest'           => [LimitToTest::class],
            /**
             * @todo Investigate why this takes 30s to complete
             */
            'NamesTest'             => [NamesTest::class],
            'OneOfTest'             => [OneOfTest::class],
            'RandConfigurationTest' => [RandConfigurationTest::class],
            'ReadmeTest'            => [ReadmeTest::class],
            'RegexTest'             => [RegexTest::class],
            'SequenceTest'          => [SequenceTest::class],
            'SetTest'               => [SetTest::class],
            'SortTest'              => [SortTest::class],
            'SubsetTest'            => [SubsetTest::class],
            'TupleTest'             => [TupleTest::class],
            'VectorTest'            => [VectorTest::class],
        ];
    }

    /**
     * @test
     */
    public function floatTests(): void
    {
        $result = $this->runTestClass(FloatTest::class);
        $result->assertHadFailures(1)
            ->assertExceptionOnTest(ExpectationFailedException::class, 'aPropertyHoldingOnlyForPositiveNumbers');
    }

    /**
     * @test
     */
    public function dateTests(): void
    {
        $this->runTestClass(DateTest::class)
            ->assertHadFailures(1)
            ->assertExceptionOnTest(ExpectationFailedException::class, 'factoryMethodRespectsDistanceBetweenDays');
    }

    /**
     * @test
     */
    public function sumTests(): void
    {
        $this->runTestClass(SumTest::class)
            ->assertHadFailures(3)
            ->assertExceptionOnTest(ExpectationFailedException::class, 'rightIdentityElement')
            ->assertExceptionOnTest(ExpectationFailedException::class, 'equalToReferencePhpImplementation')
            ->assertExceptionOnTest(ExpectationFailedException::class, 'propertyNeverSatisfied');
    }

    /**
     * @test
     */
    public function frequencyTests(): void
    {
        $this->runTestClass(FrequencyTest::class)
            ->assertHadFailures(1)
            ->assertExceptionMessageOnTestMatches(
                '/Failed asserting that (1|100|200) is identical to 0\./',
                'alwaysFails'
            );
    }

    /**
     * @test
     */
    public function suchThatTests(): void
    {
        $this->runTestClass(SuchThatTest::class)
            ->assertHadFailures(3)
            ->assertExceptionMessageOnTest(
                'Failed asserting that 43 is greater than 100.',
                'suchThatShrinkingRespectsTheCondition'
            )
            ->assertExceptionMessageOnTest(
                'Failed asserting that 0 is greater than 42.',
                'suchThatAcceptsPHPUnitConstraints'
            )
            ->assertExceptionMessageOnTest(
                'Failed asserting that 0 is greater than 100.',
                'suchThatShrinkingRespectsTheConditionButTriesToSkipOverTheNotAllowedSet'
            );
    }

    /**
     * @test
     */
    public function mapTests(): void
    {
        $this->runTestClass(MapTest::class)
            ->assertHadFailures(2)
            ->assertExceptionMessageOnTestMatches(
                '/Failed asserting that \d+ is equal to 100 or is less than 100\./',
                'shrinkingJustMappedValues'
            )
            ->assertExceptionMessageOnTestMatches(
                '/The triple sum \d+ \+ \d+ \+ \d+ is not less than 100\./',
                'shrinkingMappedValuesInsideOtherGenerators'
            );
    }
}
