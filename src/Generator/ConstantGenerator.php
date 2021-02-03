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
class ConstantGenerator implements Generator
{
    /**
     * @var TValue $value
     */
    private $value;

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
     * @return ValueCollection<TValue>
     */
    public function shrink(Value $element): ValueCollection
    {
        return new ValueCollection([new Value($this->value)]);
    }
}
