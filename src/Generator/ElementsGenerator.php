<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;

function elements(/*$a, $b, ...*/): ElementsGenerator
{
    $arguments = func_get_args();
    if (count($arguments) == 1) {
        return Generator\ElementsGenerator::fromArray($arguments[0]);
    } else {
        return Generator\ElementsGenerator::fromArray($arguments);
    }
}


class ElementsGenerator implements Generator
{
    private array $domain;

    public static function fromArray(array $domain): self
    {
        return new self($domain);
    }

    private function __construct(array $domain)
    {
        $this->domain = $domain;
    }

    public function __invoke(int $_size, RandomRange $rand)
    {
        $index = $rand->rand(0, count($this->domain) - 1);
        return GeneratedValueSingle::fromJustValue($this->domain[$index], 'elements');
    }

    /**
     * @return GeneratedValue
     */
    public function shrink(GeneratedValue $element)
    {
        return $element;
    }
}
