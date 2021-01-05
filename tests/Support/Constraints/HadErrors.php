<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

use Test\Support\TestResult;

class HadErrors extends TestSuiteConstraint
{
    protected int $errorCount;

    public function __construct(int $errorCount = 0)
    {
        $this->errorCount = $errorCount;
    }

    public function toString(): string
    {
        switch ($this->errorCount) {
            case 0:
                return 'had no errors';
            case 1:
                return 'had 1 error';
            default:
                return "had {$this->errorCount} errors";
        }
    }

    /**
     * @param TestResult $other
     */
    protected function matches($other): bool
    {
        return (int) ((array) $other->getLog()->testsuite->attributes())['@attributes']['errors'] === $this->errorCount;
    }
}
