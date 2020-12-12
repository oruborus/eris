<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;

/**
 * @param callable $outerGeneratorFactory
 */
function bind(Generator $innerGenerator, $outerGeneratorFactory): BindGenerator
{
    return new BindGenerator(
        $innerGenerator,
        $outerGeneratorFactory
    );
}

class BindGenerator implements Generator
{
    private Generator $innerGenerator;

    /**
     * @var callable $outerGeneratorFactory
     */
    private $outerGeneratorFactory;

    /**
     * @param callable $outerGeneratorFactory
     */
    public function __construct(Generator $innerGenerator, $outerGeneratorFactory)
    {
        $this->innerGenerator = $innerGenerator;
        $this->outerGeneratorFactory = $outerGeneratorFactory;
    }

    public function __invoke(int $size, RandomRange $rand)
    {
        $innerGeneratorValue = $this->innerGenerator->__invoke($size, $rand);
        $outerGenerator = call_user_func($this->outerGeneratorFactory, $innerGeneratorValue->unbox());
        $outerGeneratorValue = $outerGenerator->__invoke($size, $rand);
        return $this->packageGeneratedValueSingle(
            $outerGeneratorValue,
            $innerGeneratorValue
        );
    }

    /**
     * @return GeneratedValueSingle
     */
    public function shrink(GeneratedValue $element)
    {
        list($outerGeneratorValue, $innerGeneratorValue) = $element->input();
        // TODO: shrink also the second generator
        $outerGenerator = call_user_func($this->outerGeneratorFactory, $innerGeneratorValue->unbox());
        $shrinkedOuterGeneratorValue = $outerGenerator->shrink($outerGeneratorValue);
        return $this->packageGeneratedValueSingle(
            $shrinkedOuterGeneratorValue,
            $innerGeneratorValue
        );
    }

    private function packageGeneratedValueSingle(GeneratedValue $outerGeneratorValue, GeneratedValue $innerGeneratorValue): GeneratedValueSingle
    {
        return GeneratedValueSingle::fromValueAndInput(
            $outerGeneratorValue->unbox(),
            [
                $outerGeneratorValue,
                $innerGeneratorValue,
            ],
            'bind'
        );
    }
}
