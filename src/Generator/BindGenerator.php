<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

class BindGenerator implements Generator
{
    private Generator $innerGenerator;

    /**
     * @var callable(mixed):Generator $outerGeneratorFactory
     */
    private $outerGeneratorFactory;

    /**
     * @param callable(mixed):Generator $outerGeneratorFactory
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

        return new Value($outerGeneratorValue->unbox(), [$outerGeneratorValue, $innerGeneratorValue]);
    }

    public function shrink(Value $element): ValueCollection
    {
        list($outerGeneratorValue, $innerGeneratorValue) = $element->input();
        // TODO: shrink also the second generator
        $outerGenerator = call_user_func($this->outerGeneratorFactory, $innerGeneratorValue->unbox());
        $shrinkedOuterGeneratorValue = $outerGenerator->shrink($outerGeneratorValue)->last();

        return new ValueCollection([
            new Value($shrinkedOuterGeneratorValue->unbox(), [$shrinkedOuterGeneratorValue, $innerGeneratorValue])
        ]);
    }
}
