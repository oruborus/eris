<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

class HadNoWarnings extends HadWarnings
{
    public function __construct()
    {
        $this->warningCount = 0;
    }
}
