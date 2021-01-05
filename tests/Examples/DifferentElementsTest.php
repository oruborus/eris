<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\Generator\TupleGenerator;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function array_filter;
use function array_values;
use function Eris\Generator\bind;
use function Eris\Generator\constant;
use function Eris\Generator\elements;
use function Eris\Generator\tuple;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class DifferentElementsTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     */
    public function aTypeIsDifferentThanAnotherOne(): void
    {
        $allTypes = [
            Type::A(),
            Type::B(),
            Type::C(),
        ];

        $remove = fn (array $haystack, $needle): array => array_values(
            array_filter($haystack, fn ($candidate): bool => $candidate !== $needle)
        );

        $this
            ->forAll(
                bind(
                    elements($allTypes),
                    fn ($first): TupleGenerator => tuple(
                        constant($first),
                        elements($remove($allTypes, $first))
                    )
                )
            )
            ->then(function (array $elements) {
                $this->assertNotEquals($elements[0], $elements[1], "Several discussion types are equals");
            });
    }
}

/**
 * @internal
 */
class Type
{
    const TYPE_A = 1;
    const TYPE_B = 2;
    const TYPE_C = 3;

    private int $type;

    private function __construct($type)
    {
        $this->type = $type;
    }

    public static function A(): self
    {
        return new self(self::TYPE_A);
    }

    public static function B(): self
    {
        return new self(self::TYPE_B);
    }

    public static function C(): self
    {
        return new self(self::TYPE_C);
    }
}
