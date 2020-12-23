<?php

namespace Eris\Shrinker;

use Eris\Contracts\Generator;
use Eris\TimeLimit\FixedTimeLimit;

class ShrinkerFactory
{
    private $options;

    /**
     * @param array $options
     *  'timeLimit' => null|integer  in seconds. The maximum time that should
     *                               be allocated to a Shrinker before giving up
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param Generator[] $generators
     * @param callable $assertion
     */
    public function multiple(array $generators, $assertion): Multiple
    {
        return $this->configureShrinker(new Multiple($generators, $assertion));
    }

    private function configureShrinker(Multiple $shrinker): Multiple
    {
        if ($this->options['timeLimit'] !== null) {
            $shrinker->setTimeLimit(FixedTimeLimit::realTime((int) $this->options['timeLimit']));
        }
        return $shrinker;
    }
}
