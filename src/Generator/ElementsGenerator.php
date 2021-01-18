<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

use function array_values;

/**
 * @implements Generator<mixed>
 */
class ElementsGenerator implements Generator
{
    private array $domain;

    public static function fromArray(array $domain): self
    {
        return new self(...array_values($domain));
    }

    /**
     * @param mixed $domain
     */
    public function __construct(...$domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return Value<mixed>
     */
    public function __invoke(int $_size, RandomRange $rand): Value
    {
        $index = $rand->rand(0, count($this->domain) - 1);
        return new Value($this->domain[$index]);
    }

    /**
     * @param Value<mixed> $element
     * @return ValueCollection<mixed>
     */
    public function shrink(Value $element): ValueCollection
    {
        return new ValueCollection([$element]);
    }
}
