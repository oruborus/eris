<?php

declare(strict_types=1);

namespace Test\Support\Constraints;

class HadNoErrors extends HadErrors
{
    public function __construct()
    {
        $this->errorCount = 0;
    }
}
