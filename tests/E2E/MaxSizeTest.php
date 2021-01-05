<?php

declare(strict_types=1);

namespace Test\E2E;

use PHPUnit\Framework\ExpectationFailedException;
use Test\Examples\SizeTest;
use Test\Support\EndToEndTestCase;

class MaxSizeTest extends EndToEndTestCase
{
    /**
     * @test
     */
    public function sizeCustomization(): void
    {
        $this->runTestClass(SizeTest::class)
            ->assertHadFailures(1)
            ->assertExceptionOnTest(ExpectationFailedException::class, 'maxSizeCanBeIncreased')
            ->assertExceptionMessageOnTest('Failed asserting that 100000 is less than 100000.', 'maxSizeCanBeIncreased');
    }
}
