<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use RuntimeException;

class GeneratorCollection implements Generator
{
    /**
     * @var Generator[] $generators
     */
    private array $generators = [];

    /**
     * @param Generator[] $generators
     */
    public function __construct(array $generators = [])
    {
        $this->generators = $generators;
    }

    public function add(Generator $generator): self
    {
        $this->generators[] = $generator;

        return $this;
    }

    public function toArray(): array
    {
        return $this->generators;
    }

    /**
     * @inheritdoc
     *
     * @return Value<list<mixed>>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $values = [];
        $generatedValues = [];

        foreach ($this->generators as $generator) {
            $value = $generator($size, $rand);
            /**
             * @var mixed
             */
            $values[] = $value->value();
            $generatedValues[] = $value;
        }

        return new Value($values, $generatedValues);
    }


    /**
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function shrink(Value $element): ValueCollection
    {
        throw new RuntimeException('Do not use!');
    }
}
