<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

use PHPUnit\Framework\Constraint\Constraint;
use Test\Support\TestResult;

abstract class TestSuiteConstraint extends Constraint
{
    /**
     * @inheritdoc
     *
     * @psalm-suppress InternalMethod
     *
     * @param TestResult $other
     */
    protected function failureDescription($other): string
    {
        return $other->getName() . ' ' . $this->toString();
    }
}
