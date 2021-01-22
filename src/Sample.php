<?php

declare(strict_types=1);

namespace Eris;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;

/**
 * @template TValue
 */
class Sample
{
    public const DEFAULT_SIZE = 10;

    /**
     * @var Generator<TValue> $generator
     */
    private Generator $generator;

    private RandomRange $rand;

    private int $size;

    /**
     * @var array<TValue> $collected
     */
    private array $collected = [];

    /**
     * @template TStaticValue
     * @param Generator<TStaticValue> $generator
     * @return self<TStaticValue>
     */
    public static function of(Generator $generator, RandomRange $rand, ?int $size = null): self
    {
        return new self($generator, $rand, $size ?? self::DEFAULT_SIZE);
    }

    /**
     * @param Generator<TValue> $generator
     */
    private function __construct(Generator $generator, RandomRange $rand, int $size)
    {
        $this->generator = $generator;
        $this->rand      = $rand;
        $this->size      = $size;
    }

    /**
     * @return self<TValue>
     */
    public function repeat(int $times): self
    {
        for ($i = 0; $i < $times; $i++) {
            $this->collected[] = $this->generator->__invoke($this->size, $this->rand)->value();
        }

        return $this;
    }

    /**
     * @param Value<TValue> $value
     * @return self<TValue>
     */
    public function shrink(?Value $value = null): self
    {
        $value ??= $this->generator->__invoke($this->size, $this->rand);

        $this->collected[] = $value->value();

        while ($currentValue = $this->generator->shrink($value)->last()) {
            if ($currentValue->value() === $value->value()) {
                break;
            }

            $this->collected[] = $currentValue->value();

            $value = $currentValue;
        }

        return $this;
    }

    /**
     * @return array<TValue>
     */
    public function collected(): array
    {
        return $this->collected;
    }
}
