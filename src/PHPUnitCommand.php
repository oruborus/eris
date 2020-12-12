<?php

namespace Eris;

final class PHPUnitCommand
{
    private int $seed;
    private string $name;

    private function __construct(int $seed, string $name)
    {
        $this->seed = $seed;
        $this->name = $name;
    }

    public static function fromSeedAndName(int $seed, string $name): self
    {
        return new self(
            $seed,
            str_replace('\\', '\\\\', $name)
        );
    }

    public function __toString()
    {
        return "ERIS_SEED={$this->seed} vendor/bin/phpunit --filter '{$this->name}'";
    }
}
