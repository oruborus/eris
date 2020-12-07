<?php

namespace Eris\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OneOfGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->singleElementGenerator = new ChooseGenerator(0, 100);
        $this->size = 10;
        $this->rand = new RandomRange(new RandSource());
    }

    public function testConstructWithAnArrayOfGenerators()
    {
        $generator = new OneOfGenerator([
            $this->singleElementGenerator,
            $this->singleElementGenerator,
        ]);

        $element = $generator($this->size, $this->rand);
        $this->assertIsInt($element->unbox());
    }

    public function testConstructWithNonGenerators()
    {
        $generator = new OneOfGenerator([42, 42]);
        $element = $generator($this->size, $this->rand)->unbox();
        $this->assertEquals(42, $element);
    }

    public function testConstructWithNoArguments()
    {
        $this->expectException(InvalidArgumentException::class);

        $generator = new OneOfGenerator([]);
        $element = $generator($this->size, $this->rand);
    }
}
