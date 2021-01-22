<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

/**
 * @template TOuterValue
 * @template TInnerValue
 * @implements Generator<TOuterValue>
 */
class BindGenerator implements Generator
{
    /**
     * @var Generator<TInnerValue> $innerGenerator
     */
    private Generator $innerGenerator;

    /**
     * @var callable(mixed):Generator<TOuterValue> $outerGeneratorFactory
     */
    private $outerGeneratorFactory;

    /**
     * @param Generator<TInnerValue> $innerGenerator
     * @param callable(mixed):Generator<TOuterValue> $outerGeneratorFactory
     */
    public function __construct(Generator $innerGenerator, $outerGeneratorFactory)
    {
        $this->innerGenerator = $innerGenerator;
        $this->outerGeneratorFactory = $outerGeneratorFactory;
    }

    /**
     * @return Value<TOuterValue>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $innerGeneratorValue = $this->innerGenerator->__invoke($size, $rand);
        $outerGenerator = ($this->outerGeneratorFactory)($innerGeneratorValue->value());
        $outerGeneratorValue = $outerGenerator->__invoke($size, $rand);

        return new Value(
            $outerGeneratorValue->value(),
            [$outerGeneratorValue, $innerGeneratorValue]
        );
    }

    public function shrink(Value $element): ValueCollection
    {
        /**
         * @var array{
         *   0:Value<TOuterValue>,
         *   1:Value<TInnerValue>
         * } $input
         */
        $input = $element->input();

        [$outerGeneratorValue, $innerGeneratorValue] = $input;

        // TODO: shrink also the second generator
        $outerGenerator = ($this->outerGeneratorFactory)($innerGeneratorValue->value());
        $shrinkedOuterGeneratorValue = $outerGenerator->shrink($outerGeneratorValue)->last();

        return new ValueCollection([
            new Value(
                $shrinkedOuterGeneratorValue->value(),
                [$shrinkedOuterGeneratorValue, $innerGeneratorValue]
            )
        ]);
    }
}
