<?php

namespace Eris\Generator;

use PHPUnit\Framework\TestCase;

class GeneratedValueTest extends TestCase
{
    public function testCanBeMappedToDeriveValues()
    {
        $initialValue = GeneratedValueSingle::fromJustValue(
            3,
            'my-generator'
        );
        $this->assertEquals(
            GeneratedValueSingle::fromValueAndInput(
                6,
                $initialValue,
                'derived-generator'
            ),
            $initialValue->map(
                function ($value) {
                    return $value * 2;
                },
                'derived-generator'
            )
        );
    }

    public function testDerivedValueCanBeAnnotatedWithNewGeneratorNameWithoutBeingChanged()
    {
        $initialValue = GeneratedValueSingle::fromJustValue(
            3,
            'my-generator'
        );
        $this->assertEquals(
            GeneratedValueSingle::fromValueAndInput(
                3,
                $initialValue,
                'derived-generator'
            ),
            $initialValue->derivedIn('derived-generator')
        );
    }

    public function testCanBeRepresentedOnOutput()
    {
        $generatedValue = GeneratedValueSingle::fromValueAndInput(
            422,
            GeneratedValueSingle::fromJustValue(211),
            'map'
        );
        $this->assertIsString($generatedValue->__toString());
        $this->assertMatchesRegularExpression('/value.*422/', $generatedValue->__toString());
        $this->assertMatchesRegularExpression('/211/', $generatedValue->__toString());
        $this->assertMatchesRegularExpression('/generator.*map/', $generatedValue->__toString());
    }

    public function testCanBeIteratedUponAsASingleOption()
    {
        $generatedValue = GeneratedValueSingle::fromValueAndInput(
            422,
            GeneratedValueSingle::fromJustValue(211),
            'map'
        );
        $this->assertEquals([$generatedValue], iterator_to_array($generatedValue));
    }
}
