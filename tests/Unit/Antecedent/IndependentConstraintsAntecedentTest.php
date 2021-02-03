<?php

declare(strict_types=1);

namespace Test\Unit\Antecedent;

use Eris\Antecedent\IndependentConstraintsAntecedent;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class IndependentConstraintsAntecedentTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Antecedent\IndependentConstraintsAntecedent::__construct
     * @covers Eris\Antecedent\IndependentConstraintsAntecedent::evaluate
     *
     * @dataProvider provideValues
     *
     * @param bool[] $values
     */
    public function evaluatesToFalseIfOneOrMoreConstraintsFailOtherwiseToTrue(array $values, bool $expected): void
    {
        $dut = new IndependentConstraintsAntecedent([
            $this->isTrue(),
            $this->isTrue(),
            $this->isFalse(),
            $this->isFalse(),
        ]);

        $actual = $dut->evaluate($values);

        $this->assertSame($expected, $actual);
    }

    public function provideValues(): array
    {
        return [
            'all values correct' => [[true, true, false, false], true],
            'first value wrong'  => [[false, true, false, false], false],
            'last value wrong'   => [[true, true, false, true], false],
            'all values wrong'   => [[false, false, true, true], false],
        ];
    }
}
