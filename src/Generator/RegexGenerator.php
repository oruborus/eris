<?php

declare(strict_types=1);

namespace Eris\Generator;

use Eris\Contracts\Generator;
use Eris\Random\RandomRange;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use ReverseRegex\Lexer;
use ReverseRegex\Random\SimpleRandom;
use ReverseRegex\Parser;
use ReverseRegex\Generator\Scope;

/**
 * @implements Generator<string>
 */
class RegexGenerator implements Generator
{
    private string $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return Value<string>
     */
    public function __invoke(int $_size, RandomRange $rand): Value
    {
        $lexer = new Lexer($this->expression);
        $gen   = new SimpleRandom($rand->rand());
        $result = null;

        $parser = new Parser($lexer, new Scope(), new Scope());
        $parser->parse()->getResult()->generate($result, $gen);

        return new Value($result);
    }

    /**
     * @param Value<string> $value
     * @return ValueCollection<string>
     */
    public function shrink(Value $value): ValueCollection
    {
        return new ValueCollection([$value]);
    }
}
