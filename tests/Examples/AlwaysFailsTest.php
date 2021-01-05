<?php

declare(strict_types=1);

namespace Test\Examples;

use Eris\TestTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\elements;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class AlwaysFailsTest extends TestCase
{
    use TestTrait;

    /**
     * @test
     *
     * @throws ExpectationFailedException
     */
    public function failsNoMatterWhatIsTheInput(): void
    {
        $this
            ->forAll(
                elements(['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'])
            )
            ->then(function (string $someChar): void {
                $this->fail("This test fails by design. '{$someChar}' was passed in.");
            });
    }
}
