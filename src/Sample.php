<?php

namespace Eris;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;

class Sample
{
    const DEFAULT_SIZE = 10;

    private Generator $generator;
    private RandomRange $rand;
    private int $size;
    private array $collected = [];

    public static function of(Generator $generator, RandomRange $rand, ?int $size = null): self
    {
        return new self($generator, $rand, $size);
    }

    private function __construct(Generator $generator, RandomRange $rand, ?int $size = null)
    {
        $this->size = $size ?? self::DEFAULT_SIZE;
        $this->generator = $generator;
        $this->rand = $rand;
    }

    public function repeat(int $times): self
    {
        for ($i = 0; $i < $times; $i++) {
            $this->collected[] = $this->generator->__invoke($this->size, $this->rand)->value();
        }
        return $this;
    }

    public function shrink(?Value $nextValue = null): self
    {
        $nextValue ??= $this->generator->__invoke($this->size, $this->rand);

        $this->collected[] = $nextValue->value();
        while ($value = $this->generator->shrink($nextValue)) {
            if ($value->last()->value() === $nextValue->value()) {
                break;
            }
            $this->collected[] = $value->last()->value();

            $nextValue = $value->last();
        }
        return $this;
    }

    public function collected(): array
    {
        return $this->collected;
    }
}
