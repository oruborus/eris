<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;

function vector(int $size, Generator $elementsGenerator): VectorGenerator
{
    return new VectorGenerator($size, $elementsGenerator);
}

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
     * @return GeneratedValue<mixed>
     */
    public function __invoke(int $size, RandomRange $rand): GeneratedValue
    {
        return $this->generator->__invoke($size, $rand);
    }

    /**
     * @return GeneratedValue<mixed>
     */
    public function shrink(GeneratedValue $vector): GeneratedValue
    {
        return $this->generator->shrink($vector);
    }
}
