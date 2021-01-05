<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

use PHPUnit\Framework\Constraint\LogicalAnd;

class WasSuccessfulIgnoringWarnings extends TestSuiteConstraint
{
    public function toString(): string
    {
        return 'was successful ignoring warnings';
    }

    /**
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     */
    protected function matches($other): bool
    {
        $constraint = new LogicalAnd();
        $constraint->setConstraints([
            new HadNoErrors(),
            new HadNoFailures()
        ]);

        return $constraint->evaluate($other, '', true);
    }
}
