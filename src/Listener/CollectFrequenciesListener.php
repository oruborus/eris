<?php

declare(strict_types=1);

namespace Eris\Listener;

use Eris\Contracts\Listener;
use Exception;

use function arsort;
use function json_encode;
use function number_format;
use function round;
use function str_pad;

use const JSON_THROW_ON_ERROR;
use const PHP_EOL;
use const STR_PAD_LEFT;

class CollectFrequenciesListener extends EmptyListener
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
        if (is_null($collectFunction)) {
            $collectFunction =
                /**
                 * @param mixed ...$values
                 */
                static fn (...$values): string => json_encode($values, JSON_THROW_ON_ERROR);
        }

        $this->collectFunction = $collectFunction;
    }

    public function endPropertyVerification(
        int $ordinaryEvaluations,
        int $iterations,
        ?Exception $exception = null
    ): void {
        if (empty($this->collectedValues)) {
            return;
        }

        arsort($this->collectedValues, SORT_NUMERIC);

        $result = PHP_EOL;
        foreach ($this->collectedValues as $key => $value) {
            $frequency = round($value / $ordinaryEvaluations * 100, 2);
            $frequency = number_format($frequency, 2);
            $frequency = str_pad($frequency, 6, ' ', STR_PAD_LEFT);
            $result .= "{$frequency}%  $key" . PHP_EOL;
        }

        echo $result;
    }

    public function newGeneration(array $generation, int $iteration): void
    {
        $key = ($this->collectFunction)(...$generation);

        $this->collectedValues[$key] = ($this->collectedValues[$key] ?? 0) + 1;
    }
}
