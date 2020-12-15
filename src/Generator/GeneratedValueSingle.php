<?php

declare(strict_types=1);

namespace Eris\Generator;

use InvalidArgumentException;
use ArrayIterator;

/**
 * Parametric with respect to the type <TValue> of its value.
 * Immutable object, modifiers return a new GeneratedValueSingle instance.
 *
 * @template TValue
 * @implements GeneratedValue<TValue>
 */
final class GeneratedValueSingle implements GeneratedValue
{
    /**
     * @var TValue $value
     */
    private $value;

    /**
     * @var mixed $input
     */
    private $input;

    private ?string $generatorName;

    /**
     * A value and the input that was used to derive it.
     * The input usually comes from another Generator.
     *
     * @template TValueStatic
     * @param TValueStatic $value
     * @param mixed $input
     * @return self<TValueStatic>
     */
    public static function fromValueAndInput($value, $input, ?string $generatorName = null): self
    {
        return new self($value, $input, $generatorName);
    }

    /**
     * Input will be copied from value.
     *
     * @template TValueStatic
     * @param TValueStatic $value
     * @return self<TValueStatic>
     */
    public static function fromJustValue($value, ?string $generatorName = null): self
    {
        return new self($value, $value, $generatorName);
    }

    /**
     * @param TValue $value
     * @param mixed $input
     * @throws InvalidArgumentException
     */
    private function __construct($value, $input, ?string $generatorName)
    {
        if ($value instanceof self) {
            throw new InvalidArgumentException("It looks like you are trying to build a GeneratedValueSingle whose value is another GeneratedValueSingle. This is almost always an error as values will be passed as-is to properties and GeneratedValueSingle should be hidden from them.");
        }

        $this->value = $value;
        $this->input = $input;
        $this->generatorName = $generatorName;
    }

    /**
     * @return mixed
     */
    public function input()
    {
        return $this->input;
    }

    /**
     * @return TValue
     */
    public function unbox()
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return var_export($this, true);
    }

    public function generatorName(): ?string
    {
        return $this->generatorName;
    }

    /**
     * Produces a new GeneratedValueSingle that wraps this one,
     * and that is labelled with $generatorName.
     * $applyToValue is mapped over the value
     * to build the outer GeneratedValueSingle object $this->value field.
     *
     * @param callable $applyToValue
     */
    public function map($applyToValue, string $generatorName): self
    {
        return new self($applyToValue($this->value), $this, $generatorName);
    }

    /**
     * Basically changes the name of the Generator,
     * but without introducing an additional layer
     * of wrapping of GeneratedValueSingle objects.
     */
    public function derivedIn(string $generatorName): self
    {
        return new self($this->value, $this, $generatorName);
    }

    public function getIterator()
    {
        return new ArrayIterator([
            $this
        ]);
    }

    public function count(): int
    {
        return 1;
    }

    /**
     * @param callable $merge
     */
    public function merge(GeneratedValueSingle $another, $merge): self
    {
        if ($another->generatorName !== $this->generatorName) {
            throw new InvalidArgumentException("Trying to merge a {$this->generatorName} GeneratedValueSingle with a {$another->generatorName} GeneratedValueSingle");
        }

        return self::fromValueAndInput(
            $merge($this->value, $another->value),
            $merge($this->input, $another->input),
            $this->generatorName
        );
    }

    public function add(GeneratedValueSingle $value): GeneratedValueOptions
    {
        return new GeneratedValueOptions([$this, $value]);
    }
}
