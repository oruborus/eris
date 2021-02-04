<?php

declare(strict_types=1);

namespace Test\Unit\Listener;

use Eris\Listener\CollectFrequenciesListener;
use PHPUnit\Framework\TestCase;

use const PHP_EOL;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CollectFrequenciesListenerTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Listener\CollectFrequenciesListener::__construct
     * @covers Eris\Listener\CollectFrequenciesListener::endPropertyVerification
     *
     * @dataProvider provideCollectionFunction
     *
     * @param null|callable(mixed...):array-key $collectionFunction
     */
    public function printsNothingWhenInitializedButNoGenerationIsAdded($collectionFunction): void
    {
        $this->expectOutputString('');

        $dut = new CollectFrequenciesListener($collectionFunction);

        $dut->endPropertyVerification(10, 10);
    }

    /**
     * @return array<string, array>
     */
    public function provideCollectionFunction(): array
    {
        return [
            'default collection function' => [null],
            'custom collection function' => [static function (...$values): int {
                echo 'Unwanted output!';
                return 1;
            }]
        ];
    }

    /**
     * @test
     *
     * @covers Eris\Listener\CollectFrequenciesListener::__construct
     * @covers Eris\Listener\CollectFrequenciesListener::endPropertyVerification
     * @covers Eris\Listener\CollectFrequenciesListener::newGeneration
     *
     * @dataProvider provideSingleGenerationSet
     *
     * @param array<mixed> $generatedValue
     */
    public function collectsSingleGenerationSetValueAndPrintsIt(array $generatedValue, string $expected): void
    {
        $this->expectOutputString(PHP_EOL . "100.00%  {$expected}" . PHP_EOL);

        $dut = new CollectFrequenciesListener();

        $dut->newGeneration($generatedValue, 1);
        $dut->endPropertyVerification(1, 1);
    }

    /**
     * @return array<string, array>
     */
    public function provideSingleGenerationSet(): array
    {
        return [
            'single integer value' => [[25], '[25]'],
            'single string value'  => [['Hello'], '["Hello"]'],
            'single array value'   => [[['Hello' => 'World!']], '[{"Hello":"World!"}]'],
            'multiple values'      => [[25, 'Hello', ['Hello' => 'World!']], '[25,"Hello",{"Hello":"World!"}]'],
        ];
    }

    /**
     * @test
     *
     * @covers Eris\Listener\CollectFrequenciesListener::__construct
     * @covers Eris\Listener\CollectFrequenciesListener::endPropertyVerification
     * @covers Eris\Listener\CollectFrequenciesListener::newGeneration
     */
    public function outputsDifferentValuesInDescendingOrder(): void
    {
        $this->expectOutputString(
            PHP_EOL .
                ' 50.00%  [5]' . PHP_EOL .
                ' 25.00%  [4]' . PHP_EOL .
                ' 16.67%  [3]' . PHP_EOL .
                '  8.33%  [2]' . PHP_EOL
        );

        $dut = new CollectFrequenciesListener();

        foreach ([5, 2, 5, 5, 3, 4, 4, 5, 3, 5, 4, 5] as $iteration => $value) {
            $dut->newGeneration([$value], $iteration);
        }
        $dut->endPropertyVerification(12, 1);
    }
}
