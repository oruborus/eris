<?php

namespace Eris\Generator;

use Countable;
use IteratorAggregate;
use Stringable;

/**
 * @template T
 * @psalm-template T
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
     * @return mixed
     */
    public function unbox();

    public function generatorName(): ?string;
}
