<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

class VectorGenerator implements Generator
{
    private Generator $generator;

    /** 
     * @var class-string $elementsGeneratorClass 
     */
    private string $elementsGeneratorClass;

    public function __construct(int $size, Generator $generator)
    {
        $this->generator = new TupleGenerator(
            ($size > 0) ?
                array_fill(0, $size, $generator) :
                []
        );
        $this->elementsGeneratorClass = get_class($generator);
    }

    /**
     * @return Value<array>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        return $this->generator->__invoke($size, $rand);
    }

    /**
     * @param Value<array> $vector
     * @return ValueCollection<array>
     */
    public function shrink(Value $vector): ValueCollection
    {
        return $this->generator->shrink($vector);
    }
}
