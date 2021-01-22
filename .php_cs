<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder)
;
