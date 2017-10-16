<?php

namespace Alhoqbani\Elastic;

use Mockery;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{

    public function tearDown()
    {
        // Add Mockery expectations to assertion count.
        if (($container = \Mockery::getContainer()) !== null) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        Mockery::close();
        parent::tearDown();
    }
}