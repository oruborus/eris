<?php

declare(strict_types=1);

namespace Eris\Contracts;

use ArrayAccess;
use Countable;
use RuntimeException;

/**
 * @psalm-consistent-constructor
 */
abstract class Growth implements ArrayAccess, Countable
{
    abstract public function getMaximumSize(): int;

    abstract public function getMaximumValue(): int;

    public function __construct(int $maximum, int $limit)
    {
    }

    /**
     * @var list<int> $values
     */
    protected array $values = [];

    /**
     * @param ?int $offset
     * @param int $value
     */
    public function offsetSet($offset, $value): void
    {
    }

    /**
     * @param int $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->values[$offset]);
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset): void
    {
    }

    /**
     * @param int $offset
     */
    public function offsetGet($offset): int
    {
        return $this->values[$offset % count($this)] ?? throw new RuntimeException("Undefined Growth key {$offset}");
    }

    public function count(): int
    {
        return count($this->values);
    }
}
