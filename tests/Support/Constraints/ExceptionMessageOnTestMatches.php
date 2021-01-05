<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;
use Test\Support\TestResult;

use function count;

class ExceptionMessageOnTestMatches extends TestSuiteConstraint
{
    protected string $pattern;

    protected string $method;

    public function __construct(string $pattern, string $method)
    {
        $this->pattern = $pattern;
        $this->method  = $method;
    }

    /**
     * @inheritdoc
     * @param TestResult $other
     */
    protected function failureDescription($other): string
    {
        return "\"{$other->getName()}::{$this->toString()}";
    }

    public function toString(): string
    {
        return "{$this->method}\" threw an exception which message matches the pattern \"{$this->pattern}\"";
    }

    /**
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     *
     * @param TestResult $other
     * @throws ExpectationFailedException
     */
    public function matches($other): bool
    {
        $defects = [
            ...$other->getResult()->errors(),
            ...$other->getResult()->failures(),
            ...$other->getResult()->warnings(),
            ...$other->getResult()->risky(),
            ...$other->getResult()->skipped(),
            ...$other->getResult()->notImplemented(),
        ];
        $filter  = fn (TestFailure $failure): bool => preg_match($this->pattern, $failure->exceptionMessage()) === 1 &&
            preg_match("/::{$this->method}($|@.*)/", $failure->getTestName()) === 1;
        $list    = array_filter($defects, $filter);

        return count($list) > 0;
    }
}
