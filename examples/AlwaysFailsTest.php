<?php

use Eris\Generator;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

class AlwaysFailsTest extends TestCase
{
    use TestTrait;

    public function testFailsNoMatterWhatIsTheInput()
    {
        $this->forAll(
            Generator\elements(['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'])
        )
            ->then(function ($someChar) {
                $this->fail("This test fails by design. '$someChar' was passed in");
            });
    }
}
