<?php

declare(strict_types=1);

namespace Eris\Contracts;

use ArrayAccess;
use Countable;
use Iterator;
use RuntimeException;

use function array_keys;
use function count;
use function is_null;

/**
 * @template TElement
 * @implements ArrayAccess<array-key, TElement>
 * @implements Iterator<array-key, TElement>
 */
abstract class Collection implements Countable, ArrayAccess, Iterator
{
    /**
     * @var TElement[] $elements
     */
    protected array $elements;

    /**
     * @var array-key[] $keys
     */
    protected array $keys;

    protected int $position = 0;

    /**
     * @param TElement ...$elements
     */
    public function __construct(...$elements)
    {
        $this->elements = $elements;
        $this->keys     = array_keys($elements);
    }

    /**
     * @return TElement[]
     */
    public function all(): array
    {
        return $this->elements;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->elements[$offset]);
    }

    /**
     * @param array-key $offset
     * @return TElement 
     */
    public function offsetGet($offset)
    {
        return $this->elements[$offset] ?? throw new RuntimeException("Invalid offset {$offset}");
    }

    /**
     * @param null|array-key $offset
     * @param TElement $value
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->elements[] = $value;
        } else {
            $this->elements[$offset] = $value;
        }

        $this->keys = array_keys($this->elements);
    }

    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
        $this->keys = array_keys($this->elements);
    }

    /**
     * @return TElement
     */
    public function current()
    {
        return $this->elements[$this->keys[$this->position]];
    }

    public function next(): void
    {
        $this->position++;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->keys[$this->position]);
    }

    public function key(): string|int
    {
        return $this->keys[$this->position];
    }
}
