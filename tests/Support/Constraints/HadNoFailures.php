<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

class HadNoFailures extends HadFailures
{
    public function __construct()
    {
        $this->failureCount = 0;
    }
}
