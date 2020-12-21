<?php

declare(strict_types=1);

namespace Eris\Contracts;

interface TerminationCondition
{
    /**
     * @return boolean
     */
    public function shouldTerminate();
}
