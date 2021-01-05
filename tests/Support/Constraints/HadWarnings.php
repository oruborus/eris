<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

use Test\Support\TestResult;

class HadWarnings extends TestSuiteConstraint
{
    protected int $warningCount;

    public function __construct(int $warningCount = 0)
    {
        $this->warningCount = $warningCount;
    }

    public function toString(): string
    {
        switch ($this->warningCount) {
            case 0:
                return 'had no warnings';
            case 1:
                return 'had 1 warning';
            default:
                return "had {$this->warningCount} warnings";
        }
    }

    /**
     * @param TestResult $other
     */
    protected function matches($other): bool
    {
        return (int) ((array) $other->getLog()->testsuite->attributes())['@attributes']['warnings'] === $this->warningCount;
    }
}
