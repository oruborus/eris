<?php

declare(strict_types=1);

namespace Eris\Antecedent;

use Eris\Contracts\Antecedent;

function printableCharacter(): PrintableCharacter
{
    return new PrintableCharacter();
}

function printableCharacters(): PrintableCharacter
{
    return new PrintableCharacter();
}

class PrintableCharacter implements Antecedent
{
    /**
     * Assumes utf-8.
     *
     * @param string[] $values
     */
    public function evaluate(array $values): bool
    {
        foreach ($values as $char) {
            if (ord($char) < 32) {
                return false;
            }
            if (ord($char) === 127) { // TODO: > 127?
                return false;
            }
        }
        return true;
    }
}
