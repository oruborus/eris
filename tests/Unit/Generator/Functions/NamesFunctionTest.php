<?php

declare(strict_types=1);

namespace Test\Unit\Generator;

use Eris\Generator\NamesGenerator;

use function copy;
use function Eris\Generator\names;
use function file_put_contents;
use function getcwd;
use function sys_get_temp_dir;
use function unlink;

/**
 * @covers Eris\Generator\names
 *
 * @uses Eris\Generator\NamesGenerator
 * @uses Eris\Random\RandomRange
 * @uses Eris\Random\RandSource
 * @uses Eris\Value\Value
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class NamesFunctionTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function createsANamesGenerator(): void
    {
        $source = getcwd() . '/src/Generator/first_names.txt';
        $destination = sys_get_temp_dir() . 'first_names.bak';
        copy($source, $destination);

        file_put_contents($source, '%NAME%');

        $dut = names();
        $actual = $dut($this->size, $this->rand)->value();

        copy($destination, $source);
        unlink($destination);

        $this->assertInstanceOf(NamesGenerator::class, $dut);
        $this->assertSame('%NAME%', $actual);
    }
}
