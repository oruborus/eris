<?php

declare(strict_types=1);

namespace Eris\Listener;

use function getmypid;

function log(string $file): Log
{
    return new Log($file, 'time', getmypid());
}
