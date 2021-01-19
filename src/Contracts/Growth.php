<?php

declare(strict_types=1);

namespace Eris\Contracts;

use ArrayAccess;
use Countable;

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
    public function offsetGet($offset): ?int
    {
        $offsetModSize = $offset % count($this);

        if (isset($this[$offsetModSize])) {
            return $this->values[$offsetModSize];
        }

        [['file' => $file, 'line' => $line]] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        trigger_error("Undefined TriangularGrowth key {$offset} in {$file} on line {$line}", E_USER_WARNING);

        return null;
    }

    public function count(): int
    {
        return count($this->values);
    }
}
