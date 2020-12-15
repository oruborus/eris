<?php

namespace Eris\Generator;

use ArrayIterator;
use LogicException;
use RuntimeException;

/**
 * Parametric with respect to the type <T> of its value,
 * which should be the type parameter <T> of all the contained GeneratedValueSingle
 * instances.
 *
 * Mainly used in shrinking, to support multiple options as possibilities
 * for shrinking a GeneratedValueSingle.
 *
 * This class tends to delegate operations to its last() elements for
 * backwards compatibility. So it can be used in context where a single
 * value is expected. The last of the options is usually the more conservative
 * in shrinking, for example subtracting 1 for the IntegerGenerator.
 * 
 * @template TValue
 * @implements GeneratedValue<TValue>
 */
class GeneratedValueOptions implements GeneratedValue
{
    /**
     * @var GeneratedValueSingle<TValue>[] $generatedValues
     */
    private array $generatedValues;

    /**
     * @param GeneratedValueSingle<TValue>[] $generatedValues
     */
    public function __construct(array $generatedValues)
    {
        $this->generatedValues = $generatedValues;
    }

    /**
     * @todo Remove from
     * @see C:\Users\JahnFe\Desktop\USB\10_Projekte\eris\src\Sample.php:45
     */
    public static function mostPessimisticChoice(GeneratedValue $value): GeneratedValue
    {
        if ($value instanceof GeneratedValueOptions) {
            return $value->last();
        }
        return $value;
    }

    /**
     * @return GeneratedValueSingle<TValue>
     */
    public function first(): GeneratedValueSingle
    {
        return $this->generatedValues[0];
    }

    /**
     * @return GeneratedValueSingle<TValue>
     */
    public function last(): GeneratedValueSingle
    {
        if (count($this->generatedValues) == 0) {
            throw new LogicException("This GeneratedValueOptions is empty");
        }
        return $this->generatedValues[count($this->generatedValues) - 1];
    }

    /**
     * @param callable $applyToValue
     */
    public function map($applyToValue, string $generatorName): self
    {
        return new self(array_map(
            function (GeneratedValue $value) use ($applyToValue, $generatorName): GeneratedValue {
                return $value->map($applyToValue, $generatorName);
            },
            $this->generatedValues
        ));
    }

    public function derivedIn(string $generatorName): void
    {
        throw new RuntimeException("GeneratedValueOptions::derivedIn() is needed, uncomment it");
    }

    /**
     * @param GeneratedValueSingle<TValue> $value
     * @return self<TValue>
     */
    public function add(GeneratedValueSingle $value): self
    {
        return new self(array_merge(
            $this->generatedValues,
            [$value]
        ));
    }

    /**
     * @param GeneratedValue<TValue> $value
     * @return self<TValue>
     */
    public function remove(GeneratedValue $value): self
    {
        $generatedValues = $this->generatedValues;
        $index = array_search($value, $generatedValues);
        if ($index !== false) {
            unset($generatedValues[$index]);
        }
        return new self(array_values($generatedValues));
    }

    /**
     * @return TValue
     */
    public function unbox()
    {
        return $this->last()->unbox();
    }

    /**
     * @return mixed
     */
    public function input()
    {
        return $this->last()->input();
    }

    public function __toString(): string
    {
        return var_export($this, true);
    }

    public function generatorName(): ?string
    {
        return $this->last()->generatorName();
    }

    /**
     * @return ArrayIterator<array-key, GeneratedValueSingle<TValue>>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->generatedValues);
    }

    public function count()
    {
        return count($this->generatedValues);
    }

    /**
     * @template TValue2
     * @param self<TValue2> $generatedValueOptions
     * @param callable $merge
     */
    public function cartesianProduct(self $generatedValueOptions, $merge): self
    {
        $options = [];
        foreach ($this as $firstPart) {
            foreach ($generatedValueOptions as $secondPart) {
                $options[] = $firstPart->merge($secondPart, $merge);
            }
        }
        return new self($options);
    }
}
