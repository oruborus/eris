<?php

declare(strict_types=1);

namespace Eris\Listener;

use Eris\Contracts\Listener;
use Exception;

use const JSON_THROW_ON_ERROR;

/**
 * @param ?callable(mixed...):array-key $collectFunction
 */
function collectFrequencies($collectFunction = null): CollectFrequencies
{
    return new CollectFrequencies($collectFunction);
}

class CollectFrequencies extends EmptyListener implements Listener
{
    /**
     * @var callable(mixed...):array-key $collectFunction
     */
    private $collectFunction;

    /**
     * @var array<int> $collectedValues
     */
    private array $collectedValues = [];

    /**
     * @param ?callable(mixed...):array-key $collectFunction
     */
    public function __construct($collectFunction = null)
    {
        if ($collectFunction === null) {
            $collectFunction =
                /**
                 * @param mixed[] ...$values
                 * @return array-key
                 */
                function (...$values) {
                    if (count($values) === 1) {
                        /**
                         * @var mixed $values
                         */
                        $values = $values[0];
                    }

                    if (is_string($values) || is_integer($values)) {
                        return $values;
                    }

                    return json_encode($values, JSON_THROW_ON_ERROR);
                };
        }
        $this->collectFunction = $collectFunction;
    }

    public function endPropertyVerification(
        int $ordinaryEvaluations,
        int $iterations,
        ?Exception $exception = null
    ): void {
        arsort($this->collectedValues, SORT_NUMERIC);
        echo PHP_EOL;
        foreach ($this->collectedValues as $key => $value) {
            $frequency = round(($value / $ordinaryEvaluations) * 100, 2);
            echo "{$frequency}%  $key" . PHP_EOL;
        }
    }

    public function newGeneration(array $generation, int $iteration): void
    {
        $key = ($this->collectFunction)(...$generation);
        // TODO: check key is a correct key, identity may lead this to be a non-string and non-integer value
        // have a default for arrays and other scalars

        $this->collectedValues[$key] = $this->collectedValues[$key] ?? 0 + 1;
    }
}
