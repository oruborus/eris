<?php

declare(strict_types=1);

namespace Eris\Value;

use Stringable;

use function var_export;

/**
 * @psalm-immutable
 * @template TValue
 */
class Value implements Stringable
{
    /**
     * @var TValue $value
     */
    private $value;

    /**
     * @var mixed $input
     */
    private $input;

    /**
     * @param TValue $value
     * @param mixed $input
     */
    public function __construct($value, $input = null)
    {
        $this->value = $value;
        $this->input = $input ?? $value;
    }

    /**
     * @return TValue
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @deprecated Use Value::value() instead
     * @codeCoverageIgnore
     *
     * @return TValue
     */
    public function unbox()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function input()
    {
        return $this->input;
    }

    /**
     * @param callable(TValue):TValue $mapFn
     * @return self<TValue>
     */
    public function map($mapFn)
    {
        return new self($mapFn($this->value), $this);
    }

    /**
     * This method is currently vulnerable, as the $mergeFn expects both $value and $input to be of the same type.
     * The only caller relying on this method is Eris\Value\ValueCollection::cartesianProduct which in turn is relied
     * on only by Eris\Generator\TupleGenerator::optionsFromeTheseGenerators.
     *
     * @param self<TValue> $another
     * @param callable(TValue, TValue):TValue $mergeFn
     * @return self<TValue>
     */
    public function merge($another, $mergeFn)
    {
        /**
         * @psalm-suppress MixedArgument
         */
        return new self(
            $mergeFn($this->value, $another->value()),
            $mergeFn($this->input, $another->input())
        );
    }

    public function __toString(): string
    {
        return var_export($this, true);
    }
}
