<?php

declare(strict_types=1);

namespace Test\Unit;

use Eris\Contracts\Quantifier;
use Eris\Generator\ConstantGenerator;
use Eris\PHPUnitCommand;
use Eris\Quantifier\ForAll;
use Eris\Sample;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Annotation\DocBlock;
use ReflectionMethod;
use Test\Support\AbstractTestCase;

use function array_keys;
use function putenv;

use const PHP_EOL;

/**
 * @uses Eris\Quantifier\CanConfigureQuantifier
 * @uses Eris\Quantifier\QuantifierBuilder
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TestTraitTest extends TestCase
{
    /**
     * @test
     *
     * @coversNothing
     *
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     */
    public function setUpWillRunBeforeTestExecutionInPhpUnit(): void
    {
        $docBlock = DocBlock::ofMethod(
            new ReflectionMethod(TestTrait::class, 'erisSetup'),
            TestTrait::class
        );

        $this->assertTrue($docBlock->isToBeExecutedBeforeTest());
    }

    /**
     * @test
     *
     * @covers Eris\TestTrait::erisSetup
     *
     * @uses Eris\TestTrait::parseAnnotations
     *
     * @uses Eris\Listener\MinimumEvaluations
     */
    public function retrievesSeedFromEnvironment(): void
    {
        $seed = 123456789;
        putenv("ERIS_SEED={$seed}");

        $dut = new class() extends AbstractTestCase
        {
            use TestTrait;

            public function testMethod(): int
            {
                return $this->seed;
            }
        };
        $dut->erisSetup();

        putenv('ERIS_SEED=');

        $actual = $dut->testMethod();

        $this->assertSame($seed, $actual);
    }

    /**
     * @test
     *
     * @covers Eris\TestTrait::erisSetup
     * 
     * @uses Eris\TestTrait::parseAnnotations
     *
     * @uses Eris\Listener\MinimumEvaluations
     * @uses Eris\TerminationCondition\TimeBasedTerminationCondition
     */
    public function callsAllNeededConfigurationMethods(): void
    {
        $quantifier = $this->getMockForAbstractClass(Quantifier::class);
        $quantifier->expects($this->once())->method('withSeed');
        $quantifier->expects($this->once())->method('withRand');
        $quantifier->expects($this->once())->method('withMaximumIterations');
        $quantifier->expects($this->once())->method('withShrinkingTimeLimit');
        $quantifier->expects($this->once())->method('stopOn');
        $quantifier->expects($this->once())->method('listenTo');

        $dut = new class('testMethod') extends AbstractTestCase
        {
            use TestTrait;

            /**
             * @eris-shrink 5
             * @eris-duration PT10S
             */
            public function testMethod(Quantifier $quantifier): void
            {
                $this->getQuantifierBuilder()->build($quantifier);
            }
        };
        $dut->erisSetup();

        $dut->testMethod($quantifier);
    }

    /**
     * @test
     *
     * @coversNothing
     *
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     */
    public function tearDownWillRunAfterTestExecutionInPhpUnit(): void
    {
        $docBlock = DocBlock::ofMethod(
            new ReflectionMethod(TestTrait::class, 'erisTeardown'),
            TestTrait::class
        );

        $this->assertTrue($docBlock->isToBeExecutedAfterTest());
    }

    /**
     * @test
     *
     * @covers Eris\TestTrait::erisTeardown
     */
    public function outputsNothingWhenTestDidNotFail(): void
    {
        $this->expectOutputString('');

        $dut = new class('', false) extends AbstractTestCase
        {
            use TestTrait;
        };

        $dut->erisTeardown();
    }

    /**
     * @test
     *
     * @covers Eris\TestTrait::erisTeardown
     *
     * @uses Eris\PHPUnitCommand
     */
    public function outputsSeedDumpWhenTestFailed(): void
    {
        $this->expectOutputString(
            PHP_EOL . "Reproduce with:" . PHP_EOL . PHPUnitCommand::fromSeedAndName(123, '') . PHP_EOL
        );

        $dut = new class('', true) extends AbstractTestCase
        {
            use TestTrait;

            public function testMethod(): void
            {
                $this->seed = 123;
            }
        };
        $dut->testMethod();

        $dut->erisTeardown();
    }

    /**
     * @test
     *
     * @covers Eris\TestTrait::forAll
     *
     * @uses Eris\Antecedent\AntecedentCollection
     * @uses Eris\Generator\ConstantGenerator
     * @uses Eris\Generator\boxAll
     * @uses Eris\Listener\ListenerCollection
     * @uses Eris\Quantifier\ForAll
     * @uses Eris\Shrinker\ShrinkerFactory
     * @uses Eris\TerminationCondition\TerminationConditionCollection
     */
    public function createsForAllQuantifier(): void
    {
        /** 
         * @var TestTrait&MockObject $dut
         */
        $dut = $this->getMockForTrait(TestTrait::class);

        $actual = $dut->forAll([]);

        $this->assertInstanceOf(ForAll::class, $actual);
    }

    /**
     * @test
     *
     * @covers Eris\TestTrait::parseAnnotations
     */
    public function recognizesAllAvailableAnnotationsOnTestMethod(): void
    {
        $expected = [
            'eris-duration',
            'eris-method',
            'eris-ratio',
            'eris-repeat',
            'eris-shrink',
        ];

        $dut = new class('testMethod') extends AbstractTestCase
        {
            use TestTrait {
                parseAnnotations as public;
            }

            /**
             * @eris-duration
             * @eris-method
             * @eris-ratio
             * @eris-repeat
             * @eris-shrink
             */
            public function testMethod(): void
            {
            }
        };

        $actual = $dut->parseAnnotations();

        $this->assertEqualsCanonicalizing($expected, array_keys($actual));
    }

    /**
     * @test
     *
     * @covers Eris\TestTrait::parseAnnotations
     */
    public function recognizesAllAvailableAnnotationsOnTestCase(): void
    {
        $expected = [
            'eris-duration',
            'eris-method',
            'eris-ratio',
            'eris-repeat',
            'eris-shrink',
        ];

        $dut =
            /**
             * @eris-duration
             * @eris-method
             * @eris-ratio
             * @eris-repeat
             * @eris-shrink
             */
            new class('testMethod') extends AbstractTestCase
            {
                use TestTrait {
                    parseAnnotations as public;
                }
            };

        $actual = $dut->parseAnnotations();

        $this->assertEqualsCanonicalizing($expected, array_keys($actual));
    }

    /**
     * @test
     *
     * @covers Eris\TestTrait
     *
     * @uses Eris\Listener\MinimumEvaluations  ??
     * @uses Eris\Random\RandomRange           ??
     */
    public function annotationsOnTestMethodWillBeAppendedToExistingAnnotations(): void
    {
        $dut =
            /**
             * @eris-annotation initial
             */
            new class('testMethod') extends AbstractTestCase
            {
                use TestTrait {
                    parseAnnotations as public;
                }

                /**
                 * @eris-annotation appended
                 */
                public function testMethod(): void
                {
                }
            };

        $actual = $dut->parseAnnotations();

        $this->assertSame('appended', $actual['eris-annotation']);
    }

    /**
     * @test
     *
     * @covers Eris\TestTrait::parseAnnotations
     */
    public function missingAnnotationsWillSetToDefaultValues(): void
    {
        $expected = [
            'eris-duration' => false,
            'eris-method'   => 'rand',
            'eris-ratio'    => '50',
            'eris-repeat'   => '100',
            'eris-shrink'   => false,
        ];

        $dut = new class('testMethod') extends AbstractTestCase
        {
            use TestTrait {
                parseAnnotations as public;
            }
        };

        $actual = $dut->parseAnnotations();

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    /**
     * @test
     *
     * @covers Eris\TestTrait::sample
     * @covers Eris\TestTrait::sampleShrink
     *
     * @uses Eris\Generator\ConstantGenerator
     * @uses Eris\Sample
     * @uses Eris\Random\RandomRange
     * @uses Eris\Value\Value
     * @uses Eris\Value\ValueCollection
     */
    public function returnsSampleClasses(): void
    {
        /** 
         * @var TestTrait&MockObject $dut
         */
        $dut = $this->getMockForTrait(TestTrait::class);

        $this->assertInstanceOf(Sample::class, $dut->sample(new ConstantGenerator(5)));
        $this->assertInstanceOf(Sample::class, $dut->sampleShrink(new ConstantGenerator(5)));
    }
}
