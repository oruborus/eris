<?php

declare(strict_types=1);

namespace Eris\Contracts;

use PHPUnit\Framework\Constraint\Constraint;

interface Quantifier extends QuantifierConfiguration
{
    /**
     * @param Antecedent|Constraint|callable(mixed...):bool $firstArgument
     */
    public function when($firstArgument, Constraint ...$arguments): self;

    /**
     * @param Antecedent|Constraint|callable(mixed...):bool $firstArgument
     */
    public function and($firstArgument, Constraint ...$arguments): self;

    /**
     * @param callable(mixed...):void $assertion
     */
    public function then($assertion): self;

    /**
     * @param callable(mixed...):void $assertion
     */
    public function __invoke($assertion): self;
}
