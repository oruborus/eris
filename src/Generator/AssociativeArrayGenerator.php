<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

/**
 * @return AssociativeArrayGenerator
 */
function associative(array $generators)
{
    return new AssociativeArrayGenerator($generators);
}

class AssociativeArrayGenerator implements Generator
{
    /**
     * @var Generator[] $generators
     */
    private array $generators;
    private TupleGenerator $tupleGenerator;

    /**
     * @param Generator[] $generators
     */
    public function __construct(array $generators)
    {
        $this->generators = $generators;
        $this->tupleGenerator = new TupleGenerator(array_values($generators));
    }

    /**
     * @return Value<array>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $tuple = $this->tupleGenerator->__invoke($size, $rand);
        return $this->mapToAssociativeArray($tuple);
    }

    /**
     * @param Value<array> $element
     * @return ValueCollection<array>
     */
    public function shrink(Value $element): ValueCollection
    {
        $input = $element->input();

        if (!$input instanceof Value) {
            $input = new Value(array_values($input));
        }

        $shrunkInput = $this->tupleGenerator->shrink($input);
        return $this->mapToAssociativeArray($shrunkInput);
    }

    /**
     * @template TTuple of Value<array>|ValueCollection<array>
     * @param TTuple $tuple
     * @return TTuple
     */
    private function mapToAssociativeArray($tuple)
    {
        return $tuple->map(
            function (array $value): array {
                $associativeArray = [];
                $keys = array_keys($this->generators);
                for ($i = 0; $i < count($value); $i++) {
                    $key = $keys[$i];
                    $associativeArray[$key] = $value[$i];
                }
                return $associativeArray;
            }
        );
    }
}
