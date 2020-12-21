<?php

namespace Eris\Quantifier;

use Countable;
use OutOfBoundsException;

class Size implements Countable
{
    /** @var int[] $list */
    private array $list;

    private int $maxSize;

    public static function withTriangleGrowth(int $maximum): self
    {
        return self::generateList($maximum, __CLASS__ . '::triangleNumber');
    }

    public static function withLinearGrowth(int $maximum): self
    {
        return self::generateList($maximum, __CLASS__ . '::linearGrowth');
    }

    /**
     * @param callable $growth
     */
    private static function generateList(int $maximum, $growth): self
    {
        $sizes = [];
        for ($x = 0; $x <= $maximum; $x++) {
            $candidateSize = (int) call_user_func($growth, $x);
            if ($candidateSize <= $maximum) {
                $sizes[] = $candidateSize;
            } else {
                break;
            }
        }
        return new self($sizes, $maximum);
    }

    private static function linearGrowth(int $n): int
    {
        return $n;
    }

    /**
     * Growth which approximates (n^2)/2.
     * Returns the number of dots needed to compose a
     * triangle with n dots on a side.
     *
     * E.G.: when n=3 the function evaluates to 6
     *   .
     *  . .
     * . . .
     */
    private static function triangleNumber(int $n): int
    {
        if ($n === 0) {
            return 0;
        }
        return (int) (($n * ($n + 1)) / 2);
    }

    /**
     * @param int[] $list
     */
    private function __construct(array $list, int $maxSize)
    {
        $this->list = $list;

        $this->maxSize = $maxSize;
    }

    public function at(int $position): int
    {
        $index = $position % count($this->list);
        return $this->list[$index];
    }

    public function max(): int
    {
        if (empty($this->list)) {
            throw new OutOfBoundsException("List is empty");
        }

        return max($this->list);
    }

    public function limit(int $maximumNumber): self
    {
        $uniformSample = [];
        $factor = count($this->list) / ($maximumNumber - 1);
        for ($i = 0; $i < $maximumNumber; $i++) {
            $position = (int) min(floor($i * $factor), count($this->list) - 1);
            $uniformSample[] = $this->at($position);
        }
        return new self($uniformSample, $this->maxSize);
    }

    public function count(): int
    {
        return count($this->list);
    }

    public function getMaxSize(): int
    {
        return $this->maxSize;
    }
}
