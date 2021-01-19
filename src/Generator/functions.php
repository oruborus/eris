<?php

declare(strict_types=1);

namespace Eris\Generator;

use DateTime;
use Eris\Contracts\Generator;
use PHPUnit\Framework\Constraint\Constraint;

use function abs;
use function array_combine;
use function array_keys;
use function array_map;
use function count;
use function is_array;
use function is_string;

/**
 * @return Generator[]
 */
function ensureAreAllGenerators(array $generators): array
{
    return array_combine(
        array_keys($generators),
        array_map('Eris\Generator\ensureIsGenerator', $generators)
    );
}

/**
 * @param mixed $generator
 */
function ensureIsGenerator($generator): Generator
{
    if ($generator instanceof Generator) {
        return $generator;
    }
    return new ConstantGenerator($generator);
}

function associative(array $generators): AssociativeArrayGenerator
{
    return new AssociativeArrayGenerator($generators);
}

/**
 * @param callable(mixed):Generator $outerGeneratorFactory
 */
function bind(Generator $innerGenerator, $outerGeneratorFactory): BindGenerator
{
    return new BindGenerator($innerGenerator, $outerGeneratorFactory);
}

function bool(): BooleanGenerator
{
    return new BooleanGenerator();
}

function byte(): ChooseGenerator
{
    return new ChooseGenerator(0, 255);
}

/**
 * Generates character in the ASCII 0-127 range.
 *
 * @param array $characterSets  Only supported charset: "basic-latin"
 * @param string $encoding  Only supported encoding: "utf-8"
 */
function char(array $characterSets = ['basic-latin'], $encoding = 'utf-8'): CharacterGenerator
{
    return CharacterGenerator::ascii();
}

/**
 * Generates character in the ASCII 32-127 range, excluding non-printable ones or modifiers such as CR, LF and Tab.
 */
function charPrintableAscii(): CharacterGenerator
{
    return CharacterGenerator::printableAscii();
}

/**
 * Generates a number in the range from the lower bound to the upper bound, inclusive. 
 * The result shrinks towards smaller absolute values.
 * The order of the parameters does not matter since they are re-ordered by the generator itself.
 */
function choose(int $lowerLimit, int $upperLimit): ChooseGenerator
{
    return new ChooseGenerator($lowerLimit, $upperLimit);
}

/**
 * @param mixed $value the only value to generate
 */
function constant($value): ConstantGenerator
{
    return ConstantGenerator::box($value);
}

/**
 * @param null|string|DateTime $lowerLimit
 * @param null|string|DateTime $upperLimit
 */
function date($lowerLimit = null, $upperLimit = null): DateGenerator
{
    $lowerLimit = $lowerLimit ?? '@0';
    $lowerLimit = is_string($lowerLimit) ? new DateTime($lowerLimit) : $lowerLimit;
    $upperLimit = $upperLimit ?? '@' . (2 ** 31 - 1);
    $upperLimit = is_string($upperLimit) ? new DateTime($upperLimit) : $upperLimit;

    return new DateGenerator($lowerLimit, $upperLimit);
}

/**
 * @param mixed $arguments
 */
function elements(...$arguments): ElementsGenerator
{
    if (count($arguments) === 1) {
        $arguments = is_array($arguments[0]) ? $arguments[0] : [$arguments[0]];
    }

    return ElementsGenerator::fromArray($arguments);
}

/**
 * @param callable(mixed):bool|Constraint $filter
 */
function filter($filter, Generator $generator, int $maximumAttempts = 100): SuchThatGenerator
{
    return suchThat($filter, $generator, $maximumAttempts);
}

function float(): FloatGenerator
{
    return new FloatGenerator();
}

/**
 * @param mixed $arguments
 */
function frequency(...$arguments): FrequencyGenerator
{
    return new FrequencyGenerator($arguments);
}

/**
 * Generates a positive or negative integer (with absolute value bounded by the generation size).
 */
function int(): IntegerGenerator
{
    return new IntegerGenerator();
}

/**
 * @todo support calls like ($function . $generator)
 *
 * @param callable $function
 */
function map($function, Generator $generator): MapGenerator
{
    return new MapGenerator($function, $generator);
}

function names(): NamesGenerator
{
    return NamesGenerator::defaultDataSet();
}

function nat(): IntegerGenerator
{
    return new IntegerGenerator(fn (int $i) => abs($i));
}

/**
 * Generates a negative integer (bounded by the generation size).
 */
function neg(): IntegerGenerator
{
    return new IntegerGenerator(fn (int $i) => (-1) * (abs($i) + 1));
}

/**
 * @param mixed $arguments
 */
function oneOf(...$arguments): OneOfGenerator
{
    return new OneOfGenerator($arguments);
}

/**
 * Generates a positive integer (bounded by the generation size).
 */
function pos(): IntegerGenerator
{
    return new IntegerGenerator(fn (int $i) => abs($i) + 1);
}

/**
 * Note * and + modifiers cause an unbounded number of character to be generated
 * (up to plus infinity) and as such they are not supported.
 * Please use {1,N} and {0,N} instead of + and *.
 */
function regex(string $expression): RegexGenerator
{
    return new RegexGenerator($expression);
}

/**
 * @todo Generator::box($singleElementGenerator);
 *
 * @param mixed $singleElementGenerator
 */
function seq($singleElementGenerator): SequenceGenerator
{
    if (($singleElementGenerator instanceof Generator)) {
        return new SequenceGenerator($singleElementGenerator);
    }

    return new SequenceGenerator(new ConstantGenerator($singleElementGenerator));
}

function set(Generator $singleElementGenerator): SetGenerator
{
    return new SetGenerator($singleElementGenerator);
}

function string(): StringGenerator
{
    return new StringGenerator();
}

function subset(array $input): SubsetGenerator
{
    return new SubsetGenerator($input);
}

/**
 * @param callable(mixed):bool|Constraint $filter
 */
function suchThat($filter, Generator $generator, int $maximumAttempts = 100): SuchThatGenerator
{
    return new SuchThatGenerator($filter, $generator, $maximumAttempts);
}

/**
 * One Generator for each member of the Tuple: 
 *   tuple(Generator, Generator, Generator...)
 * Or an array of generators:
 *   tuple(array $generators)
 *
 * @param Generator[]|array{0:Generator[]} $arguments
 */
function tuple(...$arguments): TupleGenerator
{
    if (is_array($arguments[0])) {
        return new TupleGenerator($arguments[0]);
    }

    return new TupleGenerator($arguments);
}

function vector(int $size, Generator $elementsGenerator): VectorGenerator
{
    return new VectorGenerator($size, $elementsGenerator);
}
