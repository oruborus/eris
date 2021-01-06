<?php

declare(strict_types=1);

namespace Eris\Value;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

use function array_map;
use function array_search;
use function array_values;
use function count;
use function debug_backtrace;
use function end;
use function is_null;
use function key;
use function reset;
use function trigger_error;
use function var_export;

/**
 * @template TValue
 * @implements IteratorAggregate<array-key, Value<TValue>>
 */
class ValueCollection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var Value<TValue>[] $values
     */
    private array $values;

    /**
     * @param Value<TValue>[] $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function count(): int
    {
        return count($this->values);
    }
    /**
     * @return Value<TValue>[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return ArrayIterator<array-key, Value<TValue>>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->values);
    }

    /**
     * @param ?array-key $offset
     * @param Value<TValue> $value
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->values[] = $value;
        } else {
            $this->values[$offset] = $value;
        }
    }

    /**
     * @param ?array-key $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->values[$offset]);
    }

    /**
     * @param array-key $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->values[$offset]);
    }

    /**
     * @param array-key $offset
     * @return ?Value<TValue>
     */
    public function offsetGet($offset): ?Value
    {
        if (isset($this->values[$offset])) {
            return $this->values[$offset];
        }

        [['file' => $file, 'line' => $line]] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        trigger_error("Undefined ValueCollection key {$offset} in {$file} on line {$line}", E_USER_WARNING);

        return null;
    }

    public function __toString(): string
    {
        return var_export($this, true);
    }

    /**
     * @param callable(TValue):TValue $mapFn
     * @return self<TValue>
     */
    public function map($mapFn): self
    {
        $wrapper =
            /**
             * @param Value<TValue> $value
             * @return Value<TValue>
             */
            fn (Value $value): Value => new Value($mapFn($value->value()), $value);

        return new self(array_map($wrapper, $this->values));
    }

    /**
     * @param self<TValue> $another
     * @param callable(TValue, TValue):TValue $productFn
     * @return self<TValue>
     */
    public function cartesianProduct($another, $productFn): self
    {
        $product = new self();

        foreach ($this as $value1) {
            foreach ($another as $value2) {
                $product[] = $value1->merge($value2, $productFn);
            }
        }

        return $product;
    }

    /**
     * @return Value<TValue>|false
     */
    public function shift()
    {
        $value = reset($this->values);

        if (!is_null($key = key($this->values))) {
            unset($this[$key]);
            $this->values = array_values($this->values);

            return $value;
        }

        return false;
    }

    /**
     * @deprecated
     * @return ?Value<TValue>
     */
    public function first(): ?Value
    {
        $value = reset($this->values);

        if (!is_null(key($this->values))) {
            return $value;
        }

        [['file' => $file, 'line' => $line]] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        trigger_error("Undefined first element of ValueCollection in {$file} on line {$line}", E_USER_WARNING);

        return null;
    }

    /**
     * @deprecated
     * @return ?Value<TValue>
     */
    public function last(): ?Value
    {
        $value = end($this->values);

        if (!is_null(key($this->values))) {
            return $value;
        }

        [['file' => $file, 'line' => $line]] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        trigger_error("Undefined last element of ValueCollection in {$file} on line {$line}", E_USER_WARNING);

        return null;
    }

    /**
     * @param Value<TValue> $value
     * @return self<TValue>
     */
    public function add(Value $value): self
    {
        $this[] = $value;

        return $this;
    }

    /**
     * @param Value<TValue> $value
     * @return self<TValue>
     */
    public function remove(Value $value): self
    {
        if (($index = array_search($value, $this->values)) !== false) {
            unset($this[$index]);
        }

        return $this;
    }

    /**
     * @deprecated
     * @return ?TValue
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function unbox()
    {
        return $this->last()?->unbox();
    }

    /**
     * @deprecated
     * @return mixed
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function input()
    {
        return $this->last()?->input();
    }
}
