<?php

declare(strict_types=1);

namespace Test\E2E;

use Test\Support\EndToEndTestCase;

class FacadeTest extends EndToEndTestCase
{
    /**
     * @test
     */
    public function generatingIntegersWithAScript(): void
    {
        ob_start();
        require './tests/Examples/generating_integers.php';
        $output = ob_get_clean();
        $lines  = preg_split('/(\r\n|\r|\n)/', $output, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $this->assertCount(100, $lines);
    }
}
