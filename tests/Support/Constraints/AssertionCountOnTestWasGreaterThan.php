<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

use Test\Support\TestResult;

class AssertionCountOnTestWasGreaterThan extends TestSuiteConstraint
{
    protected int $count;

    protected string $method;

    public function __construct(int $count, string $method)
    {
        $this->count  = $count;
        $this->method = $method;
    }

    public function toString(): string
    {
        return "performed more than {$this->count} assertion" . ($this->count === 1 ? '' : 's');
    }

    /**
     * @param TestResult $other
     */
    protected function matches($other): bool
    {
        foreach ($other->getLog()->testsuite->testcase as $testCase) {
            if ((string) $testCase->attributes()['name'] === $this->method) {
                return (int) $testCase->attributes()['assertions'] > $this->count;
            }
        }
        return false;
    }
}
