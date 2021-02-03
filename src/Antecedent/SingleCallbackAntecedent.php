<?php

declare(strict_types=1);

namespace Eris\Antecedent;

use Eris\Contracts\Antecedent;

class SingleCallbackAntecedent implements Antecedent
{
    /**
     * @var callable(mixed...):bool $callback
     */
    private $callback;

    /**
     * @param callable(mixed...):bool $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function evaluate(array $values): bool
    {
        return ($this->callback)(...$values);
    }
}
