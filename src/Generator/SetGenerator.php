<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function array_rand;
use function array_splice;
use function count;
use function in_array;
use function rand;

/**
 * @template TInnerValue
 * @implements Generator<list<TInnerValue>>
 */
class SetGenerator implements Generator
{
    /**
     * @var Generator<TInnerValue> $generator
     */
    private Generator $generator;

    /**
     * @param Generator<TInnerValue> $generator
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }
    /**
     * @return Value<list<TInnerValue>>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $setSize = rand(0, $size);
        $set = [];
        $input = [];

        for ($tries = 0; $tries < 2 * $setSize && count($set) < $setSize; $tries++) {
            $candidateNewElement = $this->generator->__invoke($size, $rand);

            if (in_array($candidateNewElement->value(), $set, $strict = true)) {
                continue;
            }

            $set[]   = $candidateNewElement->value();
            $input[] = $candidateNewElement;
        }

        return new Value($set, $input);
    }

    /**
     * @param Value<list<TInnerValue>> $set
     * @return ValueCollection<list<TInnerValue>>
     */
    public function shrink(Value $set): ValueCollection
    {
        /**
         * @var list<Value<TInnerValue>> $input
         */
        $input = $set->input();

        if (empty($input)) {
            return new ValueCollection([$set]);
        }

        array_splice($input, array_rand($input), 1);

        /**
         * @var ValueCollection<list<TInnerValue>>
         */
        return new ValueCollection([
            new Value(
                array_map(
                    /**
                     * @param Value<TInnerValue> $element
                     * @return TInnerValue
                     */
                    fn (Value $element) => $element->value(),
                    $input
                ),
                $input
            )
        ]);
    }
}
