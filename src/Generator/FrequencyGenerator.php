<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use InvalidArgumentException;

use function array_key_first;
use function array_sum;

class FrequencyGenerator implements Generator
{
    private int $frequencySum = 0;

    /**
     * @var list<int> $stackedFrequencies
     */
    private array $stackedFrequencies = [];

    /**
     * @var list<Generator> $generators
     */
    private array $generators = [];

    /**
     * @psalm-param list<array{0: int, 1: mixed}> $generatorsWithFrequency
     */
    public function __construct(array $generatorsWithFrequency)
    {
        /**
         * @psalm-suppress MixedAssignment
         */
        foreach ($generatorsWithFrequency as [$frequency, $generator]) {
            if ($frequency === 0) {
                continue;
            }

            if ($frequency < 0) {
                throw new InvalidArgumentException('Frequency must be an integer greater than or equal to 0.');
            }

            $this->frequencySum = array_sum($this->stackedFrequencies) + $frequency;
            $this->stackedFrequencies[] = $this->frequencySum;
            $this->generators[] = box($generator);
        }

        if (empty($this->stackedFrequencies)) {
            throw new InvalidArgumentException('Cannot choose from an empty array of generators');
        }
    }

    public function __invoke(int $size, RandomRange $rand): Value
    {
        $frequencyThreshold = $rand->rand(1, $this->frequencySum);

        $index = (int) array_key_first(
            array_filter($this->stackedFrequencies, static fn (int $item): bool => $frequencyThreshold <= $item)
        );

        $generatedValue = $this->generators[$index]->__invoke($size, $rand);

        return new Value($generatedValue->value(), ['value' => $generatedValue, 'index' => $index]);
    }

    public function shrink(Value $element): ValueCollection
    {
        /**
         * @var array{"index": int, "value": Value}
         */
        ['value' => $value, 'index' => $index] = $element->input();

        $shrunkValue = $this->generators[$index]->shrink($value)->last();

        // TODO: take advantage of multiple shrinking
        return new ValueCollection([
            new Value($shrunkValue->value(), ['value' => $shrunkValue, 'index' => $index])
        ]);
    }
}
