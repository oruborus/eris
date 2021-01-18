<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function array_map;
use function array_rand;
use function array_splice;

/**
 * @template TInnerValue
 * @implements Generator<list<TInnerValue>>
 */
class SequenceGenerator implements Generator
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
        $sequenceLength = $rand->rand(0, $size);
        $vectorGenerator = new VectorGenerator($sequenceLength, $this->generator);

        /**
         * @var Value<list<TInnerValue>>
         */
        return $vectorGenerator($size, $rand);
    }

    /**
     * @param Value<list<TInnerValue>> $sequence
     * @return ValueCollection<list<TInnerValue>>
     */
    public function shrink(Value $sequence): ValueCollection
    {
        $count = count($sequence->value());

        if ($count === 0) {
            /**
             * @var ValueCollection<list<TInnerValue>>
             */
            return new ValueCollection();
        }

        /**
         * @var list<Value<TInnerValue>> $input
         */
        $input = $sequence->input();

        array_splice($input, array_rand($input), 1);

        $shrunkSequence = new Value(
            array_map(
                /**
                 * @param Value<TInnerValue> $element
                 * @return TInnerValue
                 */
                fn (Value $element) => $element->value(),
                $input
            ),
            $input
        );

        // TODO: try to shrink the elements also of longer sequences
        if ($count > 9) { // a size which is computationally acceptable
            return new ValueCollection([$shrunkSequence]);
        }

        /**
         * @var ValueCollection<list<TInnerValue>> $shrunkElements
         */
        $shrunkElements = (new VectorGenerator($count, $this->generator))->shrink($sequence);

        return new ValueCollection([$shrunkSequence, ...$shrunkElements->getValues()]);
    }
}
