<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;
use Test\Support\TestResult;

use function count;

class ExceptionMessageOnTest extends TestSuiteConstraint
{
    protected string $message;

    protected string $method;

    public function __construct(string $message, string $method)
    {
        $this->message = $message;
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
        return "{$this->method}\" threw an exception with the message \"{$this->message}\"";
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
        $filter  = fn (TestFailure $failure): bool => $failure->exceptionMessage() === $this->message &&
            preg_match("/::{$this->method}($|@.*)/", $failure->getTestName()) === 1;
        $list    = array_filter($defects, $filter);

        return count($list) > 0;
    }
}
