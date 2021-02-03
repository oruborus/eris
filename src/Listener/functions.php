<?php

declare(strict_types=1);

namespace Eris\Listener;

use function getmypid;

/**
 * @param ?callable(mixed...):array-key $collectFunction
 */
function collectFrequencies($collectFunction = null): CollectFrequenciesListener
{
    return new CollectFrequenciesListener($collectFunction);
}

function log(string $file): LogListener
{
    return new LogListener($file, 'time', getmypid());
}
