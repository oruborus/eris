<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

/**
 * @template TValue
 * @implements Generator<TValue>
 */
class MapGenerator implements Generator
{
    /**
     * @var callable(TValue):TValue $map
     */
    private $map;

    /**
     * @var Generator<TValue> $generator
     */
    private Generator $generator;

    /**
     * @param callable(TValue):TValue $map
     * @param Generator<TValue> $generator
     */
    public function __construct($map, Generator $generator)
    {
        $this->map = $map;
        $this->generator = $generator;
    }

    /**
     * @return Value<TValue>
     */
    public function __invoke(int $_size, RandomRange $rand): Value
    {
        $input = $this->generator->__invoke($_size, $rand);

        return $input->map($this->map);
    }

    /**
     * @param Value<TValue> $value
     * @return ValueCollection<TValue>
     */
    public function shrink(Value $value): ValueCollection
    {
        /**
         * @var Value<TValue> $input
         */
        $input = $value->input();

        $shrunkInput = $this->generator->shrink($input);

        return $shrunkInput->map($this->map);
    }
}
