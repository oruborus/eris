<?php

namespace Eris\Generator;

use InvalidArgumentException;
use ArrayIterator;
use Generator;

/**
 * Parametric with respect to the type <T> of its value.
 * Immutable object, modifiers return a new GeneratedValueSingle instance.
 *
 * @template TT
 * @psalm-template TT
 */
final class GeneratedValueSingle implements GeneratedValue // TODO? interface ShrunkValue extends IteratorAggregate[, Countable]
{
    /**
     * @var TT $value
     */
    private $value;

    /**
     * @var mixed $input
     */
    private $input;

    private ?string $generatorName;

    private array $annotations;

    /**
     * A value and the input that was used to derive it.
     * The input usually comes from another Generator.
     * @template T
     * @param T $value
     * @param mixed $input
     * @param null|string $generatorName  'tuple'
     * @return GeneratedValueSingle
     */
    public static function fromValueAndInput($value, $input, $generatorName = null)
    {
        return new self($value, $input, $generatorName);
    }

    /**
     * Input will be copied from value.
     *
     * @template T
     * @param T $value
     * @param string $generatorName  'tuple'
     * @return GeneratedValueSingle<T>
     */
    public static function fromJustValue($value, $generatorName = null)
    {
        return new self($value, $value, $generatorName);
    }

    /**
     * @param TT $value
     * @param mixed $input
     */
    private function __construct($value, $input, ?string $generatorName, array $annotations = [])
    {
        if ($value instanceof self) {
            throw new InvalidArgumentException("It looks like you are trying to build a GeneratedValueSingle whose value is another GeneratedValueSingle. This is almost always an error as values will be passed as-is to properties and GeneratedValueSingle should be hidden from them.");
        }
        $this->value = $value;
        $this->input = $input;
        $this->generatorName = $generatorName;
        $this->annotations = $annotations;
    }

    /**
     * @return mixed
     */
    public function input()
    {
        return $this->input;
    }

    /**
     * @return TT
     */
    public function unbox()
    {
        return $this->value;
    }

    public function __toString()
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
     * @param callable $applyToValue
     */
    public function map($applyToValue, string $generatorName): self
    {
        return new self(
            $applyToValue($this->value),
            $this,
            $generatorName
        );
    }

    /**
     * Basically changes the name of the Generator,
     * but without introducing an additional layer
     * of wrapping of GeneratedValueSingle objects.
     *
     * @param string $generatorName  'tuple', 'vector'
     * @return GeneratedValueSingle
     */
    public function derivedIn($generatorName)
    {
        return $this->map(
            /**
             * @param mixed $value
             * @return mixed
             */
            function ($value) {
                return $value;
            },
            $generatorName
        );
    }

    public function getIterator()
    {
        return new ArrayIterator([
            $this
        ]);
    }

    public function count()
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
            $merge($this->unbox(), $another->unbox()),
            $merge($this->input(), $another->input()),
            $this->generatorName
        );
    }

    public function add(GeneratedValueSingle $value): GeneratedValueOptions
    {
        return new GeneratedValueOptions([
            $this,
            $value,
        ]);
    }
}
