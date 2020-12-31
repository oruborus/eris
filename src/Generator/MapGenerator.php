<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

class MapGenerator implements Generator
{
    /**
     * @var callable $map
     */
    private $map;
    private Generator $generator;

    /**
     * @param callable $map
     */
    public function __construct($map, Generator $generator)
    {
        $this->map = $map;
        $this->generator = $generator;
    }

    public function __invoke(int $_size, RandomRange $rand): Value
    {
        $input = $this->generator->__invoke($_size, $rand);

        return $input->map($this->map);
    }

    public function shrink(Value $value): ValueCollection
    {
        $input = $value->input();

        if (!$input instanceof Value) {
            $input = new Value($input);
        }

        $shrunkInput = $this->generator->shrink($input);

        return $shrunkInput->map($this->map);
    }
}
