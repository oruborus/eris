<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\RegexGenerator;
use Eris\Value\Value;
use Eris\Value\ValueCollection;

/**
 * @uses Eris\Random\RandSource
 * @uses Eris\Random\RandomRange
 * @uses Eris\Value\Value
 * @uses Eris\Value\ValueCollection
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class RegexGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     *
     * @covers Eris\Generator\RegexGenerator::__construct
     * @covers Eris\Generator\RegexGenerator::__invoke
     *
     * @dataProvider provideSupportedRegexes
     */
    public function generatesOnlyValuesThatMatchTheRegex(string $expression): void
    {
        $dut = new RegexGenerator($expression);

        for ($i = 0; $i < 100; $i++) {
            $value = $dut($this->size, $this->rand)->value();

            $this->assertMatchesRegularExpression("/{$expression}/", $value);
        }
    }

    public function provideSupportedRegexes(): array
    {
        return [
            // [".{0,100}"] sometimes generates NULL
            'exactly 24 alphanumerical characters'               => ["[a-z0-9]{24}"],
            '1 - 5 alphabetical characters'                      => ["[a-z]{1,5}"],
            'a single alphabetical character'                    => ["^[a-z]$"],
            'a character "a", "b" or "c"'                        => ["a|b|c"],
            'a digit folowed by whitespace and a word character' => ["\d\s\w"],
        ];
    }

    /**
     * @test
     *
     * @covers Eris\Generator\RegexGenerator::shrink
     *
     * @uses Eris\Generator\RegexGenerator::__construct
     */
    public function shrinkingIsNotImplementedYet(): void
    {
        $dut = new RegexGenerator(".*");
        $word = new Value("something");

        $actual = $dut->shrink($word);

        $this->assertEquals(new ValueCollection([$word]), $actual);
    }
}
