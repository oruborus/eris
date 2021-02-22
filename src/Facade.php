<?php

declare(strict_types=1);

namespace Eris;

class Facade
{
    use TestTrait {
        /**
         * Seems to emit MissingConstructor for psalm
         * @see https://github.com/vimeo/psalm/issues/173
         */
        erisSetup as __construct;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getName(bool $withDataSet = true): string
    {
        return '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function hasFailed(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function toString(): string
    {
        return '';
    }
}
