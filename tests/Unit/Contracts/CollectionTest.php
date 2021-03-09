<?php

declare(strict_types=1);

namespace Test\Unit\Contracts;

use Eris\Contracts\Collection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CollectionTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::all
     */
    public function aListOfElementsCanBeCollectedAndRetrieved(): void
    {
        $elements = [1, 2, 3];

        $dut = $this->getMockForAbstractClass(Collection::class, [...$elements]);

        $actual = $dut->all();

        $this->assertSame($elements, $actual);
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::count
     */
    public function collectedElementsCanbeCounted(): void
    {
        $elements = [1, 2, 3];

        $dut = $this->getMockForAbstractClass(Collection::class, [...$elements]);

        $this->assertCount(3, $dut);
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::offsetExists
     */
    public function existenceOfSpecificOffsetCanBeChecked(): void
    {
        $elements = [1, 2, 3];

        $dut = $this->getMockForAbstractClass(Collection::class, [...$elements]);

        $this->assertTrue(isset($dut[0]));
        $this->assertFalse(isset($dut[3]));
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::offsetGet
     */
    public function existingElementsCanBeRetrievedByOffset(): void
    {
        $elements = [1, 2, 3];

        $dut = $this->getMockForAbstractClass(Collection::class, [...$elements]);

        $this->assertSame(3, $dut[2]);
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::offsetGet
     */
    public function retrievingWithAnInvalidOffsetThrowsRuntimeException(): void
    {
        $this->expectException(RuntimeException::class);

        $elements = [1, 2, 3];

        $dut = $this->getMockForAbstractClass(Collection::class, [...$elements]);

        $dut[5];
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::offsetSet
     *
     * @uses Eris\Contracts\Collection::all
     */
    public function newElementsCanBeAppended(): void
    {
        $elements = [1, 2, 3];

        $dut = $this->getMockForAbstractClass(Collection::class, [...$elements]);

        $dut[] = 4;

        $this->assertSame([1, 2, 3, 4], $dut->all());
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::offsetSet
     *
     * @uses Eris\Contracts\Collection::all
     */
    public function newElementsCanBeAppendedWithDefinedOffset(): void
    {
        $elements = [1, 2, 3];

        $dut = $this->getMockForAbstractClass(Collection::class, [...$elements]);

        $dut[7] = 4;

        $this->assertSame([1, 2, 3, 7 => 4], $dut->all());
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::offsetSet
     *
     * @uses Eris\Contracts\Collection::all
     */
    public function elementsWithExistingOffsetsGetOverwritten(): void
    {
        $elements = [1, 2, 3];

        $dut = $this->getMockForAbstractClass(Collection::class, [...$elements]);

        $dut[1] = 4;

        $this->assertSame([1, 4, 3], $dut->all());
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::offsetUnset
     *
     * @uses Eris\Contracts\Collection::all
     * @uses Eris\Contracts\Collection::next
     * @uses Eris\Contracts\Collection::key
     */
    public function elementsCanBeUnset(): void
    {
        $elements = [1, 2, 3];

        $dut = $this->getMockForAbstractClass(Collection::class, [...$elements]);

        $dut->next();
        unset($dut[1]);

        $this->assertSame([0 => 1, 2 => 3], $dut->all());
        $this->assertSame(2, $dut->key());
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::current
     */
    public function firstElementIsTheCurrentOneAfterInitialization(): void
    {
        $elements = ['first' => 1, 'second' => 2, 'third' => 3];

        $dut = $this->getMockForAbstractClass(Collection::class, $elements);

        $this->assertSame(1, $dut->current());
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::current
     * @covers Eris\Contracts\Collection::next
     *
     * @covers Eris\Contracts\Collection::offsetSet
     */
    public function currentElementGetsSetInOrderOfInsertion(): void
    {
        $elements = ['first' => 1];

        $dut = $this->getMockForAbstractClass(Collection::class, $elements);

        $dut['second'] = 2;
        $dut->next();

        $this->assertSame(2, $dut->current());
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::current
     * @covers Eris\Contracts\Collection::next
     * @covers Eris\Contracts\Collection::rewind
     *
     * @covers Eris\Contracts\Collection::offsetSet
     */
    public function currentElementCanBeResetToFirst(): void
    {
        $elements = ['first' => 1];

        $dut = $this->getMockForAbstractClass(Collection::class, $elements);

        $dut['second'] = 2;
        $dut->next();

        $this->assertSame(2, $dut->current());

        $dut->rewind();

        $this->assertSame(1, $dut->current());
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::next
     * @covers Eris\Contracts\Collection::valid
     *
     * @covers Eris\Contracts\Collection::offsetSet
     */
    public function existenceOfCurrentElementCanBeChecked(): void
    {
        $elements = ['first' => 1];

        $dut = $this->getMockForAbstractClass(Collection::class, $elements);

        $dut['second'] = 2;
        $dut->next();

        $this->assertTrue($dut->valid());

        $dut->next();

        $this->assertFalse($dut->valid());
    }

    /**
     * @test
     *
     * @covers Eris\Contracts\Collection::__construct
     * @covers Eris\Contracts\Collection::next
     * @covers Eris\Contracts\Collection::key
     */
    public function keyOfCurrentElementCanBeRetrieved(): void
    {
        $elements = ['first' => 1, 'second' => 2];

        $dut = $this->getMockForAbstractClass(Collection::class, $elements);

        $this->assertSame('first', $dut->key());

        $dut->next();

        $this->assertSame('second', $dut->key());
    }
}
