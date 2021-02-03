<?php

declare(strict_types=1);

namespace Eris\Antecedent;

use Eris\Contracts\Antecedent;

use function is_string;
use function ord;

class PrintableCharacter implements Antecedent
{
    /**
     * Assumes utf-8.
     */
    public function evaluate(array $values): bool
    {
        foreach ($values as $char) {
            if (!is_string($char)) {
                return false;
            }

            if (ord($char) < 32) {
                return false;
            }

            if (ord($char) > 126) {
                return false;
            }
        }

        return true;
    }
}
