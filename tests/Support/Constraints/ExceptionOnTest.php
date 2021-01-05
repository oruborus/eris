<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;
use Test\Support\TestResult;
use Throwable;

use function count;

class ExceptionOnTest extends TestSuiteConstraint
{
    /**
     * @var class-string<Throwable> $exception
     */
    protected string $exception;

    protected string $method;

    /**
     * @param class-string<Throwable> $exception
     */
    public function __construct(string $exception, string $method)
    {
        $this->exception = $exception;
        $this->method    = $method;
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
        return "{$this->method}\" threw \"{$this->exception}\"";
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

        $filter = function (TestFailure $failure): bool {
            if (preg_match("/::{$this->method}($|@.*)/", $failure->getTestName()) !== 1) {
                return false;
            }

            $exception = $failure->thrownException();

            if ($exception instanceof $this->exception) {
                return true;
            }

            if (
                $exception instanceof ExceptionWrapper &&
                $exception->getClassName() === $this->exception
            ) {
                return true;
            }

            return false;
        };

        $list = array_filter($defects, $filter);

        return count($list) > 0;
    }
}
