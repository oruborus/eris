<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use PHPUnit\Framework\Constraint\Constraint;

/**
 * @template TValue
 * @implements Generator<TValue>
 */
class SuchThatGenerator implements Generator
{
    /**
     * @var callable(TValue):bool
     */
    private $filter;

    /**
     * @var Generator<TValue> $generator
     */
    private Generator $generator;

    private int $maximumAttempts;

    /**
     * @param callable(TValue):bool|Constraint $filter
     * @param Generator<TValue> $generator
     */
    public function __construct($filter, Generator $generator, int $maximumAttempts = 100)
    {
        if ($filter instanceof Constraint) {
            /**
             * @var callable(TValue):bool
             */
            $filter =
                /**
                 * @psalm-suppress NullableReturnStatement
                 * @psalm-suppress InvalidNullableReturnType
                 *
                 * @param Value<TValue> $value
                 */
                fn ($value): bool => $filter->evaluate($value, '', true);
        }

        $this->filter = $filter;

        $this->generator = $generator;

        $this->maximumAttempts = $maximumAttempts;
    }

    public function __invoke(int $size, RandomRange $rand): Value
    {
        for ($attempts = 0; $attempts < $this->maximumAttempts; $attempts++) {
            $value = $this->generator->__invoke($size, $rand);

            if (($this->filter)($value->value())) {
                return $value;
            }
        }

        throw new SkipValueException(
            "Tried to satisfy predicate {$attempts} times, but could not generate a good value. " .
                "You should try to improve your generator to make it more likely to output good values, " .
                "or to use a less restrictive condition."
        );
    }

    public function shrink(Value $value): ValueCollection
    {
        $shrunkValues = new ValueCollection([$value]);

        for ($attempts = 0; $attempts < $this->maximumAttempts; $attempts++) {
            $shrunkValues = $this->generator->shrink($shrunkValues->last());


            $filtered = new ValueCollection();
            foreach ($shrunkValues as $shrunkValue) {
                if (($this->filter)($shrunkValue->value())) {
                    $filtered[] = $shrunkValue;
                }
            }

            if (count($filtered)) {
                return $filtered;
            }
        }

        return new ValueCollection([$value]);
    }
}
