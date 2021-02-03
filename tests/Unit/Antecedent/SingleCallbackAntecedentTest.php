<?php

declare(strict_types=1);

namespace Test\Unit\Antecedent;

use Eris\Antecedent\SingleCallbackAntecedent;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SingleCallbackAntecedentTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Antecedent\SingleCallbackAntecedent::__construct
     * @covers Eris\Antecedent\SingleCallbackAntecedent::evaluate
     *
     * @dataProvider provideValues
     *
     * @param bool[] $values
     */
    public function evaluatesToBasedOnProvidedCallback(array $values, bool $expected): void
    {
        $dut = new SingleCallbackAntecedent(static fn (...$values) => $values[0]);

        $actual = $dut->evaluate($values);

        $this->assertSame($expected, $actual);
    }

    public function provideValues(): array
    {
        return [
            'failing value'  => [[false, true, false, false], false],
            'correct value'  => [[true, true, false, false], true],
        ];
    }
}
