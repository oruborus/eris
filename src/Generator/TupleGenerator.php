<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function Eris\Generator\ensureAreAllGenerators;

class TupleGenerator implements Generator
{
    /**
     * @var Generator[] $generators
     */
    private array $generators;
    private int $numberOfGenerators;

    /**
     * @param Generator[] $generators
     */
    public function __construct(array $generators)
    {
        $this->generators = ensureAreAllGenerators($generators);
        $this->numberOfGenerators = count($generators);
    }

    /**
     * @return Value<array>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $input = array_map(
            function (Generator $generator) use ($size, $rand) {
                return $generator($size, $rand);
            },
            $this->generators
        );

        return new Value(
            array_map(
                /**
                 * @return mixed
                 */
                fn (Value $value) => $value->unbox(),
                $input
            ),
            $input
        );
    }

    /**
     * TODO: recursion may cause problems here as other Generators
     * like Vector use this with a high number of elements.
     * Rewrite to something that does not overflow the stack
     * @return ValueCollection<array>
     */
    private function optionsFromTheseGenerators(array $generators, array $inputSubset): ValueCollection
    {
        if (!$inputSubset[0] instanceof Value) {
            $inputSubset[0] = new Value($inputSubset[0]);
        }
        $optionsForThisElement = $generators[0]->shrink($inputSubset[0]);
        // so that it can be used in combination with other shrunk elements
        $optionsForThisElement = $optionsForThisElement->add($inputSubset[0]);

        $options = new ValueCollection();
        foreach ($optionsForThisElement as $value) {
            $options[] = new Value([$value->unbox()], [$value]);
        }

        if (count($generators) == 1) {
            return $options;
        }

        return $options->cartesianProduct(
            $this->optionsFromTheseGenerators(
                array_slice($generators, 1),
                array_slice($inputSubset, 1)
            ),
            function (array $first, array $second) {
                return array_merge($first, $second);
            }
        );
    }

    /**
     * @param Value<array> $tuple
     * @return ValueCollection<array>
     */
    public function shrink(Value $tuple): ValueCollection
    {
        $input = $tuple->input();

        return $this->optionsFromTheseGenerators($this->generators, $input)->remove($tuple);
    }

    /**
     * @return Generator[]
     *
     * @psalm-return array<array-key, Generator>
     */
    private function ensureAreAllGenerators(array $generators): array
    {
        return array_map(
            function ($generator) {
                if ($generator instanceof Generator) {
                    return $generator;
                }
                return new ConstantGenerator($generator);
            },
            $generators
        );
    }

    private function domainsTupleAsString(): string
    {
        $domainOfElements = '(';
        foreach ($this->generators as $generator) {
            $domainOfElements .= get_class($generator);
            $domainOfElements .= ',';
        }
        return substr($domainOfElements, 0, -1) . ')';
    }
}
