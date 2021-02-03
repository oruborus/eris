<?php

declare(strict_types=1);

namespace Test\Unit\Listener;

use Eris\Listener\CollectFrequenciesListener;
use PHPUnit\Framework\TestCase;

use function Eris\Listener\collectFrequencies;

/**
 * @covers Eris\Listener\collectFrequencies
 *
 * @uses Eris\Listener\CollectFrequenciesListener
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CollectFrequenciesFunctionTest extends TestCase
{
    /**
     * @test
     */
    public function createsACollectFrequenciesListener(): void
    {
        $dut = collectFrequencies();

        $this->assertInstanceOf(CollectFrequenciesListener::class, $dut);
    }
}
