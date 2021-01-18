<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function count;
use function file;
use function levenshtein;
use function mb_strlen;

use const PHP_INT_MAX;

/**
 * @implements Generator<string>
 */
class NamesGenerator implements Generator
{
    /**
     * @var list<string> $list
     */
    private array $list;

    private int $minLength = PHP_INT_MAX;

    /**
     * @link http://data.bfontaine.net/names/firstnames.txt
     *
     * @return self
     */
    public static function defaultDataSet(): self
    {
        return new self(file(__DIR__ . "/first_names.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    }

    /**
     * @param list<string> $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;

        foreach ($this->list as $candidate) {
            $candidateLength = mb_strlen($candidate);
            $this->minLength = $candidateLength < $this->minLength ? $candidateLength : $this->minLength;
        }
    }

    /**
     * @return Value<string>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        if ($size < $this->minLength) {
            return new Value('');
        }

        $index = -1;

        while (strlen($this->list[$index = $rand->rand(0, count($this->list) - 1)]) > $size);

        return new Value($this->list[$index]);
    }

    /**
     * @param Value<string> $element
     * @return ValueCollection<string>
     */
    public function shrink(Value $element): ValueCollection
    {
        $value = $element->value();
        $size  = mb_strlen($value) - 1;

        $primeDistance = PHP_INT_MAX;
        $primeCandidate = $value;

        foreach ($this->list as $candidate) {
            if (strlen($candidate) !== $size) {
                continue;
            }

            $distance = levenshtein($value, $candidate);

            if ($distance >= $primeDistance) {
                continue;
            }

            $primeCandidate = $candidate;
            $primeDistance  = $distance;
        }

        return new ValueCollection([new Value($primeCandidate)]);
    }
}
