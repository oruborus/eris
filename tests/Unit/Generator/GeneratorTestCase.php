<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use PHPUnit\Framework\TestCase;

class GeneratorTestCase extends TestCase
{
    protected int $size;

    protected RandomRange $rand;

    protected function setUp(): void
    {
        $this->size = 10;
        $this->rand = new RandomRange(new RandSource());
    }
}
