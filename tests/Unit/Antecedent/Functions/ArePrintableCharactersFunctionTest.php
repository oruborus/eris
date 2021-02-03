<?php

declare(strict_types=1);

namespace Test\Unit\Antecedent;

use Eris\Antecedent\PrintableCharacterAntecedent;
use PHPUnit\Framework\TestCase;

use function Eris\Antecedent\arePrintableCharacters;

/**
 * @covers Eris\Antecedent\arePrintableCharacters
 *
 * @uses Eris\Antecedent\PrintableCharacterAntecedent
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ArePrintableCharactersFunctionTest extends TestCase
{
    /**
     * @test
     */
    public function createsAnPrintableCharacterAntecedent(): void
    {
        $dut = arePrintableCharacters();

        $this->assertInstanceOf(PrintableCharacterAntecedent::class, $dut);
    }
}
