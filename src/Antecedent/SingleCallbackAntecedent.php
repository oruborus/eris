<?php

namespace Eris\Antecedent;

use Eris\Contracts\Antecedent;

class SingleCallbackAntecedent implements Antecedent
{
    /**
     * @var callable $callback
     */
    private $callback;

    /**
     * @param callable $callback
     */
    public static function from($callback): self
    {
        return new self($callback);
    }

    /**
     * @param callable $callback
     */
    private function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function evaluate(array $values)
    {
        return call_user_func_array($this->callback, $values);
    }
}
