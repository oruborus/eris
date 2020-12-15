<?php

namespace Eris\Generator;

use Countable;
use IteratorAggregate;
use Stringable;

/**
 * @template TValue
 * @extends IteratorAggregate<array-key, self<TValue>>
 */
interface GeneratedValue extends IteratorAggregate, Countable, Stringable
{
    /**
     * @param callable $applyToValue
     */
    public function map($applyToValue, string $generatorName): self;

    /**
     * @return mixed
     */
    public function input();

    /**
     * @return TValue
     */
    public function unbox();

    public function generatorName(): ?string;
}
