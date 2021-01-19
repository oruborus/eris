<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function Eris\cartesianProduct;

/**
 * @template TInnerValue
 * @implements Generator<array<TInnerValue>>
 */
class AssociativeArrayGenerator implements Generator
{
    /**
     * @var Generator[] $generators
     */
    private array $generators;

    /**
     * @param Generator[] $generators
     */
    public function __construct(array $generators)
    {
        $this->generators = ensureAreAllGenerators($generators);
    }

    /**
     * @psalm-suppress MixedAssignment
     *
     * @return Value<array<TInnerValue>>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $value = [];
        $input = [];

        foreach ($this->generators as $key => $generator) {
            $generated = $generator($size, $rand);
            $value[$key] = $generated->value();
            $input[$key] = $generated;
        }

        return new Value($value, $input);
    }

    /**
     * @psalm-suppress MixedAssignment
     *
     * @param Value<array<TInnerValue>> $element
     * @return ValueCollection<array<TInnerValue>>
     */
    public function shrink(Value $element): ValueCollection
    {
        $elementValue = $element->value();

        /**
         * @var Value<array>[] $elementInput
         */
        $elementInput = $element->input();

        $shrunkValues = [];
        foreach ($elementInput as $key => $value) {
            $shrunkValues[$key] = $this->generators[$key]->shrink($value)->add($value)->getValues();
        }

        /**
         * @var ValueCollection<array> $result
         */
        $result = new ValueCollection();
        foreach (cartesianProduct($shrunkValues) as $input) {
            $value = [];
            foreach ($input as $key => $member) {
                $value[$key] = $member->value();
            }

            if ($value == $elementValue) {
                continue;
            }

            $result[] = new Value($value, $input);
        }

        return count($result) ? $result : new ValueCollection([$element]);
    }
}
