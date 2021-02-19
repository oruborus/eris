<?php

declare(strict_types=1);

namespace Test\Unit\Listener;

use Eris\Contracts\Listener;
use Eris\Listener\ListenerCollection;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @uses Eris\Contracts\Collection
 */
class ListenerCollectionTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Listener\ListenerCollection::startPropertyVerification
     * @covers Eris\Listener\ListenerCollection::endPropertyVerification
     * @covers Eris\Listener\ListenerCollection::newGeneration
     * @covers Eris\Listener\ListenerCollection::failure
     * @covers Eris\Listener\ListenerCollection::shrinking
     */
    public function listenersCanBeAddedAndAllTheirInterfaceMethodCanGetCalled(): void
    {
        $listener1 = $this->getMockForAbstractClass(Listener::class);
        $listener1->expects($this->once())->method('startPropertyVerification');
        $listener1->expects($this->once())->method('endPropertyVerification');
        $listener1->expects($this->once())->method('newGeneration');
        $listener1->expects($this->once())->method('failure');
        $listener1->expects($this->once())->method('shrinking');
        $listener2 = $this->getMockForAbstractClass(Listener::class);
        $listener2->expects($this->once())->method('startPropertyVerification');
        $listener2->expects($this->once())->method('endPropertyVerification');
        $listener2->expects($this->once())->method('newGeneration');
        $listener2->expects($this->once())->method('failure');
        $listener2->expects($this->once())->method('shrinking');

        $dut = new ListenerCollection($listener1, $listener2);

        $dut->startPropertyVerification();
        $dut->endPropertyVerification(0, 0);
        $dut->newGeneration([], 0);
        $dut->failure([], new Exception());
        $dut->shrinking([]);
    }

    /**
     * @test
     *
     * @covers Eris\Listener\ListenerCollection::removeListenerOfType
     *
     * @uses Eris\Listener\ListenerCollection::startPropertyVerification
     */
    public function listenersTypesCanBeRemovedFromCollection(): void
    {
        $listener1 = $this->getMockForAbstractClass(Listener::class, [], 'Jimmy');
        $listener1->expects($this->once())->method('startPropertyVerification');
        $listener2 = $this->getMockForAbstractClass(Listener::class, [], 'Jonny');
        $listener2->expects($this->never())->method('startPropertyVerification');

        $dut = new ListenerCollection($listener1, $listener2);

        $dut->removeListenerOfType('Jonny');

        $dut->startPropertyVerification();
    }
}
