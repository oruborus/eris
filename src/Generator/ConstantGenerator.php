<?php

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

/**
 * @param mixed $value  the only value to generate
 * @return ConstantGenerator
 */
function constant($value)
{
    return ConstantGenerator::box($value);
}
/**
 * @template TValue
 */
class ConstantGenerator implements Generator
{
    /**
     * @var TValue $value
     */
    private $value;

    /**
     * @template TStaticValue
     * @param TStaticValue $value
     * @return self<TStaticValue>
     */
    public static function box($value): self
    {
        return new self($value);
    }

    /**
     * @param TValue $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return Value<TValue>
     */
    public function __invoke(int $_size, RandomRange $rand): Value
    {
        return new Value($this->value);
    }

    /**
     * @param Value<TValue> $element
     * @return ValueCOllection<TValue>
     */
    public function shrink(Value $element): ValueCollection
    {
        return new ValueCollection([new Value($this->value)]);
    }
}
