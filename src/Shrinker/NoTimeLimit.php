<?php

namespace Eris\Shrinker;

use Eris\Contracts\TimeLimit;

class NoTimeLimit implements TimeLimit
{
    public function start()
    {
    }

    public function hasBeenReached()
    {
        return false;
    }

    public function __toString()
    {
        return 'no time limit';
    }
}
