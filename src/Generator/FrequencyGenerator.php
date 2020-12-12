<?php

namespace Eris\Generator;

use Eris\Generator;
use InvalidArgumentException;
use Eris\Random\RandomRange;
use Exception;

/**
 * @return FrequencyGenerator
 */
function frequency(/*$frequencyAndGenerator, $frequencyAndGenerator, ...*/)

{
    return new FrequencyGenerator(func_get_args());
}

class FrequencyGenerator implements Generator
{
    /**
     * @psalm-var (array{"frequency":int, "generator":Generator})[] $generators
     */
    private array $generators;

    /**
     * @psalm-param (array{0:int, 1:mixed})[] $generatorsWithFrequency
     */
    public function __construct(array $generatorsWithFrequency)
    {
        if (empty($generatorsWithFrequency)) {
            throw new InvalidArgumentException(
                'Cannot choose from an empty array of generators'
            );
        }
        $this->generators = array_reduce(
            $generatorsWithFrequency,
            /**
             * @psalm-param (array{0?:positive-int, 1?:mixed, "frequency":positive-int, "generator":Generator})[] $generators
             * @psalm-param (array{0:positive-int, 1:mixed}) $generatorWithFrequency
             */
            function ($generators, $generatorWithFrequency): array {
                list($frequency, $generator) = $generatorWithFrequency;
                $frequency = $this->ensureIsFrequency($generatorWithFrequency[0]);
                $generator = ensureIsGenerator($generatorWithFrequency[1]);
                if ($frequency > 0) {
                    $generators[] = [
                        'generator' => $generator,
                        'frequency' => $frequency,
                    ];
                }
                return $generators;
            },
            []
        );
    }

    public function __invoke(int $size, RandomRange $rand)
    {
        list($index, $generator) = $this->pickFrom($this->generators, $rand);
        $originalValue = $generator->__invoke($size, $rand);
        return GeneratedValueSingle::fromValueAndInput(
            $originalValue->unbox(),
            [
                'value' => $originalValue,
                'generator' => $index,
            ],
            'frequency'
        );
    }

    /**
     * @return GeneratedValueSingle
     */
    public function shrink(GeneratedValue $element)
    {
        $input = $element->input();
        $originalGeneratorIndex = $input['generator'];
        $shrinkedValue = $this->generators[$originalGeneratorIndex]['generator']->shrink($input['value']);

        // TODO: take advantage of multiple shrinking
        return GeneratedValueSingle::fromValueAndInput(
            $shrinkedValue->unbox(),
            [
                'value' => $shrinkedValue,
                'generator' => $originalGeneratorIndex,
            ],
            'frequency'
        );
    }

    /**
     * @psalm-param (array{"frequency":int, "generator":Generator})[] $generators
     * @return array  two elements: index and Generator object
     */
    private function pickFrom(array $generators, RandomRange $rand)
    {
        $acc = 0;
        $frequencies = $this->frequenciesFrom($generators);
        $random = $rand->rand(1, array_sum($frequencies));
        foreach ($generators as $index => $generator) {
            $acc += $generator['frequency'];
            if ($random <= $acc) {
                return [$index, $generator['generator']];
            }
        }
        throw new Exception(
            'Unable to pick a generator with frequencies: ' . var_export($frequencies, true)
        );
    }

    /**
     * @psalm-param (array{"frequency":int, "generator":Generator})[] $generators
     * @return int[]
     */
    private function frequenciesFrom($generators): array
    {
        return array_map(
            /**
             * @psalm-param (array{"frequency":int, "generator":Generator}) $generatorWithFrequency
             */
            function ($generatorWithFrequency): int {
                return $generatorWithFrequency['frequency'];
            },
            $generators
        );
    }

    private function ensureIsFrequency(int $frequency): int
    {
        if ($frequency < 0) {
            throw new InvalidArgumentException(
                'Frequency must be an integer greater or equal than 0, given: ' . var_export($frequency, true)
            );
        }
        return $frequency;
    }
}
