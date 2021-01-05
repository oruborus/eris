<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

use Test\Support\TestResult;

class HadFailures extends TestSuiteConstraint
{
    protected int $failureCount;

    public function __construct(int $failureCount = 0)
    {
        $this->failureCount = $failureCount;
    }

    public function toString(): string
    {
        switch ($this->failureCount) {
            case 0:
                return 'had no failures';
            case 1:
                return 'had 1 failure';
            default:
                return "had {$this->failureCount} failures";
        }
    }

    /**
     * @param TestResult $other
     */
    protected function matches($other): bool
    {
        return (int) ((array) $other->getLog()->testsuite->attributes())['@attributes']['failures'] === $this->failureCount;
    }
}
