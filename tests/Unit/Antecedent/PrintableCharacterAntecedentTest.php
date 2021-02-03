<?php

declare(strict_types=1);

namespace Test\Unit\Antecedent;

use Eris\Antecedent\PrintableCharacterAntecedent;
use PHPUnit\Framework\TestCase;

use function chr;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class PrintableCharacterAntecedentTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Antecedent\PrintableCharacterAntecedent::evaluate
     *
     * @dataProvider provideValues
     *
     * @param array<mixed> $values
     */
    public function returnsFalseIfProvidedValuesAreNotOfTypeString(array $values): void
    {
        $dut = new PrintableCharacterAntecedent();

        $actual = $dut->evaluate($values);

        $this->assertFalse($actual);
    }

    public function provideValues(): array
    {
        return [
            'integer' => [[1]],
            'float'   => [[1.0]],
            'array'   => [[['a']]],
            'bool'    => [[true]],
            'object'  => [[(object)['property' => 'A']]],
        ];
    }

    /**
     * @test
     *
     * @covers Eris\Antecedent\PrintableCharacterAntecedent::evaluate
     */
    public function returnsFalseIfProvidedCharactersAreOutsideThePrintableRange(): void
    {
        $dut = new PrintableCharacterAntecedent();

        $this->assertFalse($dut->evaluate([chr(31)]));
        $this->assertFalse($dut->evaluate([chr(127)]));
    }

    /**
     * @test
     *
     * @covers Eris\Antecedent\PrintableCharacterAntecedent::evaluate
     */
    public function returnsTrueOtherwise(): void
    {
        $dut = new PrintableCharacterAntecedent();

        $actual = $dut->evaluate([
            ' ', '@', '`', '!', 'A', 'a', '"', 'B', 'b', '#', 'C', 'c', '$', 'D', 'd', '%',
            'E', 'e', '&', 'F', 'f', '\'', 'G', 'g', '(', 'H', 'h', ')', 'I', 'i', '*', 'J',
            'j', '+', 'K', 'k', ',', 'L', 'l', '-', 'M', 'm', '.', 'N', 'n', '/', 'O', 'o',
            '0', 'P', 'p', '1', 'Q', 'q', '2', 'R', 'r', '3', 'S', 's', '4', 'T', 't', '5',
            'U', 'u', '6', 'V', 'v', '7', 'W', 'w', '8', 'X', 'x', '9', 'Y', 'y', ':', 'Z',
            'z', ';', '[', '{', '<', '\\', '|', '=', ']', '}', '>', '^', '~', '?', '_'
        ]);

        $this->assertTrue($actual);
    }
}
