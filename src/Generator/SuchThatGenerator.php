<?php

namespace Eris\Generator;

use Eris\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use LogicException;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use Traversable;

/**
 * @param callable|Constraint $filter
 */
function filter($filter, Generator $generator, int $maximumAttempts = 100): SuchThatGenerator
{
    return suchThat($filter, $generator, $maximumAttempts);
}

/**
 * @param callable|Constraint $filter
 */
function suchThat($filter, Generator $generator, int $maximumAttempts = 100): SuchThatGenerator
{
    return new SuchThatGenerator($filter, $generator, $maximumAttempts);
}

class SuchThatGenerator implements Generator
{
    /**
     * @var callable|Constraint $filter
     */
    private $filter;
    private Generator $generator;
    private int $maximumAttempts;

    /**
     * @param callable|Constraint $filter
     */
    public function __construct($filter, Generator $generator, int $maximumAttempts = 100)
    {
        $this->filter = $filter;
        $this->generator = $generator;
        $this->maximumAttempts = $maximumAttempts;
    }

    public function __invoke(int $size, RandomRange $rand): Value
    {
        $value = $this->generator->__invoke($size, $rand);
        $attempts = 0;
        while (!$this->predicate($value)) {
            if ($attempts >= $this->maximumAttempts) {
                throw new SkipValueException("Tried to satisfy predicate $attempts times, but could not generate a good value. You should try to improve your generator to make it more likely to output good values, or to use a less restrictive condition. Last generated value was: " . $value->__toString());
            }
            $value = $this->generator->__invoke($size, $rand);
            $attempts++;
        }
        return $value;
    }

    public function shrink(Value $value): ValueCollection
    {
        $shrunk = $this->generator->shrink($value);
        $attempts = 0;

        $filtered = new ValueCollection();
        while (!count($filtered = $this->filterForPredicate($shrunk))) {
            if ($attempts >= $this->maximumAttempts) {
                return new ValueCollection([$value]);
            }
            $shrunk = $this->generator->shrink($shrunk->last());
            $attempts++;
        }

        return $filtered;
    }

    private function filterForPredicate(ValueCollection $options): ValueCollection
    {
        $goodOnes = new ValueCollection();
        foreach ($options as $option) {
            if ($this->predicate($option)) {
                $goodOnes[] = $option;
            }
        }
        return $goodOnes;
    }

    private function predicate(Value $value): bool
    {
        if ($this->filter instanceof Constraint) {
            try {
                $this->filter->evaluate($value->unbox());
                return true;
            } catch (ExpectationFailedException $e) {
                return false;
            }
        }

        // if (is_callable($this->filter)) {
        return (bool) call_user_func($this->filter, $value->unbox());
        // }

        throw new LogicException("Specified filter does not seem to be of the correct type. Please pass a callable or a PHPUnit\Framework\Constraint instead of " . var_export($this->filter, true));
    }
}
