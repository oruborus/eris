<?php

declare(strict_types=1);

namespace Test\Unit;

use Eris\PHPUnitCommand;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class PHPUnitCommandTest extends TestCase
{
    /**
     * @test
     *
     * @covers Eris\PHPUnitCommand::__construct
     * @covers Eris\PHPUnitCommand::__toString
     * @covers Eris\PHPUnitCommand::fromSeedAndName
     *
     * @dataProvider provideCommands
     *
     * @param class-string $name
     */
    public function itCanComposeFrom(string $name, string $expected): void
    {
        $command = (string) PHPUnitCommand::fromSeedAndName(42, $name);

        $this->assertSame($expected, $command);
    }

    /**
     * @return array<string, array>
     */
    public function provideCommands(): array
    {
        return [
            'test method in global namespace' => [
                'Foo::testBar',
                'ERIS_SEED=42 vendor/bin/phpunit --filter \'Foo::testBar\''
            ],
            'fully qualified namespace test method' => [
                'Foo\\Bar::testBaz',
                'ERIS_SEED=42 vendor/bin/phpunit --filter \'Foo\\\\Bar::testBaz\''
            ]
        ];
    }
}
