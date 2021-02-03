<?php

declare(strict_types=1);

namespace Test\Unit\Antecedent;

use Eris\Antecedent\PrintableCharacterAntecedent;
use PHPUnit\Framework\TestCase;

use function Eris\Antecedent\isPrintableCharacter;

/**
 * @covers Eris\Antecedent\isPrintableCharacter
 *
 * @uses Eris\Antecedent\PrintableCharacterAntecedent
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class IsPrintableCharacterFunctionTest extends TestCase
{
    /**
     * @test
     */
    public function createsAnPrintableCharacterAntecedent(): void
    {
        $dut = isPrintableCharacter();

        $this->assertInstanceOf(PrintableCharacterAntecedent::class, $dut);
    }
}
