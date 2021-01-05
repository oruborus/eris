<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\Facade;
use function Eris\Generator\int;
use function var_export;

require __DIR__ . '/../../vendor/autoload.php';

$eris = new Facade();

$eris
    ->forAll(
        int()
    )
    ->then(function (int $integer): void {
        echo var_export($integer, true) . PHP_EOL;
    });
