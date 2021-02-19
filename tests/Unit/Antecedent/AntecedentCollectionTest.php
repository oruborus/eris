<?php

declare(strict_types=1);

namespace Test\Unit\Listener;

use Eris\Antecedent\AntecedentCollection;
use Eris\Contracts\Antecedent;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @uses Eris\Contracts\Collection
 */
class AntecedentCollectionTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Antecedent\AntecedentCollection::evaluate
     */
    public function antecedentsCanBeAddedAndTheirEvaluationStatusCanBeRetrieved(): void
    {
        $antecedent1 = $this->getMockForAbstractClass(Antecedent::class);
        $antecedent1->expects($this->exactly(4))->method('evaluate')->willReturn(true, true, false, false);
        $antecedent2 = $this->getMockForAbstractClass(Antecedent::class);
        $antecedent2->expects($this->exactly(2))->method('evaluate')->willReturn(true, false, true, false);

        $dut = new AntecedentCollection($antecedent1, $antecedent2);

        $this->assertTrue($dut->evaluate([]));
        $this->assertFalse($dut->evaluate([]));
        $this->assertFalse($dut->evaluate([]));
        $this->assertFalse($dut->evaluate([]));
    }
}
