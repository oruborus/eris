<?php

namespace Eris;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Facade
{
    use TestTrait;

    public function __construct()
    {
        $this->erisSetupBeforeClass();
        $this->erisSetup();
    }

    protected function getName(bool $withDataSet = true): string
    {
        return '';
    }

    public function hasFailed(): bool
    {
        return false;
    }

    public function toString(): string
    {
        return '';
    }
}
