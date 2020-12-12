<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;

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

    public function __invoke(int $size, RandomRange $rand)
    {
        $tuple = $this->tupleGenerator->__invoke($size, $rand);
        return $this->mapToAssociativeArray($tuple);
    }

    /**
     * @return GeneratedValue
     */
    public function shrink(GeneratedValue $element)
    {
        $input = $element->input();
        $shrunkInput = $this->tupleGenerator->shrink($input);
        return $this->mapToAssociativeArray($shrunkInput);
    }

    private function mapToAssociativeArray(GeneratedValue $tuple): GeneratedValue
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
            },
            'associative'
        );
    }
}
