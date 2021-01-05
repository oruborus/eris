<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function var_export;
use function Eris\Generator\choose;
use function Eris\Generator\elements;
use function Eris\Generator\int;
use function Eris\Generator\float;
use function Eris\Generator\frequency;
use function Eris\Generator\nat;
use function Eris\Generator\neg;
use function Eris\Generator\oneOf;
use function Eris\Generator\pos;
use function Eris\Generator\seq;
use function Eris\Generator\string;
use function Eris\Generator\tuple;
use function Eris\Generator\vector;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class GeneratorSamplesTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     *
     * @dataProvider provideGenerators
     *
     * @todo Remove output
     */
    public function generateSample(string $description, $generator): void
    {
        echo PHP_EOL;
        echo $description . " with size 10";

        $sample = $this->sample($generator);

        $this->assertIsArray($sample->collected());

        $this->prettyPrint($sample->collected());
    }

    public function provideGenerators(): array
    {
        return [
            'Generator\int'   => ['Generator\int', int()],
            'Generator\neg'   => ['Generator\neg', neg()],
            'Generator\nat'   => ['Generator\nat', nat()],
            'Generator\pos'   => ['Generator\pos', pos()],
            'Generator\float' => ['Generator\float', float()],
            'Generator\choose(30, 9000)' => ['Generator\choose(30, 9000)', choose(30, 9000)],
            'Generator\tuple(Generator\int, Generator\neg, Generator\string' => [
                'Generator\tuple(Generator\int, Generator\neg, Generator\string',
                tuple(int(), neg(), string())
            ],
            'Generator\seq(Generator\string)' => ['Generator\seq(Generator\string)', seq(string())],
            'Generator\vector(12, Generator\neg)' => ['Generator\vector(12, Generator\neg)', vector(12, neg())],
            'Generator\elements(10, \'hello-world\', [1, 2])' => [
                'Generator\elements(10, \'hello-world\', [1, 2])',
                elements(10, 'hello-world', [1, 2])
            ],
            'Generator\oneOf([Generator\pos, Generator\neg, Generator\float])' => [
                'Generator\oneOf([Generator\pos, Generator\neg, Generator\float])',
                oneOf(pos(), neg(), float())
            ],
            'Generator\frequency([3, Generator\pos], [7, Genertor\string])' => [
                'Generator\frequency([3, Generator\pos], [7, Genertor\string])',
                frequency([3, pos()], [7, string()])
            ],
        ];
    }

    private function prettyPrint(array $samples)
    {
        echo PHP_EOL;
        foreach ($samples as $sample) {
            echo var_export($sample, true) . PHP_EOL;
        }
        echo PHP_EOL;
    }
}
