<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;

/**
 * TODO: support calls like ($function . $generator)
 * @param callable $function
 */
function map($function, Generator $generator): MapGenerator
{
    return new MapGenerator($function, $generator);
}

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

    public function __invoke(int $_size, RandomRange $rand)
    {
        $input = $this->generator->__invoke($_size, $rand);
        return $input->map(
            $this->map,
            'map'
        );
    }

    public function shrink(GeneratedValue $value)
    {
        $input = $value->input();
        $shrunkInput = $this->generator->shrink($input);
        return $shrunkInput->map(
            $this->map,
            'map'
        );
    }
}
