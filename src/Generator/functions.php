<?php

namespace Eris\Generator;

use Eris\Generator;

/**
 * @return Generator[]
 */
function ensureAreAllGenerators(array $generators): array
{
    return array_map('Eris\Generator\ensureIsGenerator', $generators);
}
/**
 * @param mixed $generator
 */
function ensureIsGenerator($generator): Generator
{
    if ($generator instanceof Generator) {
        return $generator;
    }
    return new ConstantGenerator($generator);
}
