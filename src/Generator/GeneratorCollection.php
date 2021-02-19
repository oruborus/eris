<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Collection;
use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use RuntimeException;

/**
 * @extends Collection<Generator>
 */
class GeneratorCollection extends Collection implements Generator
{
    /**
     * @inheritdoc
     *
     * @return Value<list<mixed>>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $values = [];
        $generatedValues = [];

        foreach ($this->elements as $generator) {
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
