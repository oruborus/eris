<?php

declare(strict_types=1);

namespace Eris\Growth;

use Eris\Contracts\Growth;

use function floor;
use function is_null;
use function max;
use function min;

final class TriangularGrowth extends Growth
{
    private int $maximumSize;

    public function __construct(int $maximumSize, ?int $limit = null)
    {
        $this->maximumSize = $maximumSize;

        $this->generateCandidates();

        if (is_null($limit)) {
            return;
        }

        $this->limitCandidates($limit);
    }

    private function generateCandidates(): void
    {
        for ($x = 0; $x <= $this->maximumSize; $x++) {
            /**
             * @var int $candidateSize
             */
            $candidateSize = ($x * ($x + 1)) / 2;

            if ($candidateSize > $this->maximumSize) {
                break;
            }

            $this->values[] = $candidateSize;
        }
    }

    private function limitCandidates(int $limit): void
    {
        $uniformSample = [];
        $factor = count($this->values) / ($limit - 1);

        for ($i = 0; $i < $limit; $i++) {
            $position = (int) min(floor($i * $factor), count($this->values) - 1);
            $uniformSample[] = $this->values[$position];
        }

        $this->values = $uniformSample;
    }

    public function getMaximumSize(): int
    {
        return $this->maximumSize;
    }

    public function getMaximumValue(): int
    {
        return max($this->values ?: [0]);
    }
}
