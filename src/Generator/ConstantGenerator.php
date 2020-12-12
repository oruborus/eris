<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;

/**
 * @param mixed $value  the only value to generate
 * @return ConstantGenerator
 */
function constant($value)
{
    return ConstantGenerator::box($value);
}

class ConstantGenerator implements Generator
{
    /**
     * @var mixed $value
     */
    private $value;

    /**
     * @param mixed $value
     */
    public static function box($value): self
    {
        return new self($value);
    }

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __invoke(int $_size, RandomRange $rand)
    {
        return GeneratedValueSingle::fromJustValue($this->value, 'constant');
    }

    /**
     * @return GeneratedValueSingle
     */
    public function shrink(GeneratedValue $element)
    {
        return GeneratedValueSingle::fromJustValue($this->value, 'constant');
    }
}
