<?php

declare(strict_types=1);

namespace Test\Unit\Antecedent;

use Eris\Antecedent\PrintableCharacter;
use PHPUnit\Framework\TestCase;

use function Eris\Antecedent\isPrintableCharacter;

/**
 * @covers Eris\Antecedent\isPrintableCharacter
 *
 * @uses Eris\Antecedent\PrintableCharacter
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

        $this->assertInstanceOf(PrintableCharacter::class, $dut);
    }
}
