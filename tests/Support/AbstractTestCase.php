<?php

declare(strict_types=1);

namespace Test\Support;

abstract class AbstractTestCase
{
    public function __construct(protected string $name = '', protected bool $hasFailed = false)
    {
    }

    protected function getName(bool $withDataSet = true): string
    {
        return $this->name;
    }

    public function hasFailed(): bool
    {
        return $this->hasFailed;
    }

    public function toString(): string
    {
        return '';
    }
}
