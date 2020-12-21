<?php

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

/**
 * @psalm-suppress TypeDoesNotContainType
 * TODO: Generator::box($singleElementGenerator);
 */
function seq(Generator $singleElementGenerator): SequenceGenerator
{
    if (!($singleElementGenerator instanceof Generator)) {
        $singleElementGenerator = new ConstantGenerator($singleElementGenerator);
    }
    return new SequenceGenerator($singleElementGenerator);
}

class SequenceGenerator implements Generator
{
    private $singleElementGenerator;

    public function __construct(Generator $singleElementGenerator)
    {
        $this->singleElementGenerator = $singleElementGenerator;
    }

    /**
     * @return Value<array>
     */
    public function __invoke(int $size, RandomRange $rand): Value
    {
        $sequenceLength = $rand->rand(0, $size);
        return $this->vector($sequenceLength)->__invoke($size, $rand);
    }

    /**
     * @param Value<array> $sequence
     * @return ValueCollection<array>
     */
    public function shrink(Value $sequence): ValueCollection
    {
        $options = new ValueCollection();
        if (count($sequence->unbox()) > 0) {
            $options[] = $this->shrinkInSize($sequence);
            // TODO: try to shrink the elements also of longer sequences
            if (count($sequence->unbox()) < 10) {
                // a size which is computationally acceptable
                $shrunkElements = $this->shrinkTheElements($sequence);
                foreach ($shrunkElements as $shrunkValue) {
                    $options[] = $shrunkValue;
                }
            }
        }

        return $options;
    }

    /**
     * @param Value<array> $sequence
     * @return Value<array>
     */
    private function shrinkInSize(Value $sequence): Value
    {
        if (count($sequence->unbox()) === 0) {
            return $sequence;
        }

        $input = $sequence->input();
        $indexOfElementToRemove = array_rand($input);
        unset($input[$indexOfElementToRemove]);
        $input = array_values($input);

        return new Value(
            array_map(
                /**
                 * @return mixed
                 */
                fn (Value $element) => $element->unbox(),
                $input
            ),
            $input
        );
    }

    /**
     * @return ValueCollection<array>
     */
    private function shrinkTheElements(Value $sequence): ValueCollection
    {
        return $this->vector(count($sequence->unbox()))->shrink($sequence);
    }

    private function vector(int $size): VectorGenerator
    {
        return new VectorGenerator($size, $this->singleElementGenerator);
    }
}
