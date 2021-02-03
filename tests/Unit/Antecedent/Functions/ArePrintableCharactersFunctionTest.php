<?php

declare(strict_types=1);

namespace Test\Unit\Antecedent;

use Eris\Antecedent\PrintableCharacter;
use PHPUnit\Framework\TestCase;

use function Eris\Antecedent\arePrintableCharacters;

/**
 * @covers Eris\Antecedent\arePrintableCharacters
 *
 * @uses Eris\Antecedent\PrintableCharacter
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

        $this->assertInstanceOf(PrintableCharacter::class, $dut);
    }
}
