<?php

declare(strict_types=1);

namespace Eris\Shrinker;

use Eris\Contracts\Generator;
use Eris\Contracts\Shrinker;
use Eris\Generator\GeneratorCollection;
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
     * @param callable(mixed...):void $assertion
     */
    public function multiple(GeneratorCollection $generators, $assertion): Shrinker
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
