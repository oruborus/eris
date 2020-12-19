<?php

namespace Eris\Generator;

use Eris\Random\RandomRange;
use Eris\Random\RandSource;
use Eris\Value\Value;
use Eris\Value\ValueCollection;
use PHPUnit\Framework\TestCase;

class RegexGeneratorTest extends TestCase
{
    public static function supportedRegexes()
    {
        return [
            // [".{0,100}"] sometimes generates NULL
            ["[a-z0-9]{24}"],
            ["[a-z]{1,5}"],
            ["^[a-z]$"],
            ["a|b|c"],
            ["\d\s\w"],
        ];
    }

    protected function setUp(): void
    {
        $this->size = 10;
        $this->rand = new RandomRange(new RandSource());
    }

    /**
     * @dataProvider supportedRegexes
     */
    public function testGeneratesOnlyValuesThatMatchTheRegex($expression)
    {
        $generator = new RegexGenerator($expression);
        for ($i = 0; $i < 100; $i++) {
            $value = $generator($this->size, $this->rand)->unbox();
            $this->assertMatchesRegularExpression("/{$expression}/", $value);
        }
    }

    public function testShrinkingIsNotImplementedYet()
    {
        $generator = new RegexGenerator(".*");
        $word = new Value("something");
        $this->assertEquals(new ValueCollection([$word]), $generator->shrink($word));
    }
}
