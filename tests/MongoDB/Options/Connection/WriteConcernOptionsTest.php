<?php

namespace Tequilla\MongoDB\Tests\Options\Connection;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\Connection\WriteConcernOptions;
use PHPUnit\Framework\TestCase;

class WriteConcernOptionsTest extends TestCase
{
    /**
     * @covers WriteConcernOptions::configureOptions
     * @uses \Symfony\Component\OptionsResolver\OptionsResolver
     * @expectedException \Tequilla\MongoDB\Exception\InvalidArgumentException
     * @expectedExceptionMessage Option "wtimeoutMS" will not get applied until "w" > 1
     */
    public function testWtimeoutMSnotAllowedWhenWriteConcernIsNotSet()
    {
        $resolver = new OptionsResolver();
        WriteConcernOptions::configureOptions($resolver);
        $resolver->resolve(['wtimeoutMS' => 1000]);
    }
}
