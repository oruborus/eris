<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

use Test\Support\TestResult;

class ExecutionTimeOnTestWasGreaterThan extends TestSuiteConstraint
{
    protected float $time;

    protected string $method;

    public function __construct(int|float $time, string $method)
    {
        $this->time   = (float) $time;
        $this->method = $method;
    }

    public function toString(): string
    {
        return "was executed in more than {$this->time} second" . ($this->time === 1.0 ? '' : 's');
    }

    /**
     * @param TestResult $other
     */
    protected function matches($other): bool
    {
        foreach ($other->getLog()->testsuite->testcase as $testCase) {
            if ((string) $testCase->attributes()['name'] === $this->method) {
                return (float) $testCase->attributes()['time'] > $this->time;
            }
        }
        return false;
    }
}
