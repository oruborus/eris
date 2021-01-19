<?php

declare(strict_types=1);

namespace Eris\Shrinker;

use Eris\Contracts\Generator;
use Eris\Contracts\Shrinker;
use Eris\TimeLimit\FixedTimeLimit;

use function is_null;

class ShrinkerFactory
{
    private ?int $timeLimit = null;

    public function __construct(?int $timeLimit = null)
    {
        $this->timeLimit = $timeLimit;
    }

    /**
     * @param list<Generator<mixed>> $generators
     * @param callable $assertion
     */
    public function multiple(array $generators, $assertion): Shrinker
    {
        return $this->configureShrinker(new Multiple($generators, $assertion));
    }

    private function configureShrinker(Shrinker $shrinker): Shrinker
    {
        if (!is_null($this->timeLimit)) {
            $shrinker->settimeLimit(
                FixedTimeLimit::realTime($this->timeLimit)
            );
        }

        return $shrinker;
    }
}
