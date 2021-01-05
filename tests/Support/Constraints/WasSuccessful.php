<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

use PHPUnit\Framework\Constraint\LogicalAnd;

class WasSuccessful extends TestSuiteConstraint
{
    public function toString(): string
    {
        return 'was successful';
    }

    /**
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     */
    protected function matches($other): bool
    {
        $constraint = new LogicalAnd();
        $constraint->setConstraints([
            new WasSuccessfulIgnoringWarnings(),
            new HadNoWarnings()
        ]);

        return $constraint->evaluate($other, '', true);
    }
}
