<?php

namespace Tequila\MongoDB\Tests\Command;

use PHPUnit\Framework\TestCase;
use Tequila\MongoDB\Command\Aggregate;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Tests\Traits\DatabaseAndCollectionNamesTrait;

class AggregateTest extends TestCase
{
    use DatabaseAndCollectionNamesTrait;

    /**
     * @covers Aggregate::__construct()
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $pipeline cannot be empty
     */
    public function testConstructorWithEmptyPipeline()
    {
        new Aggregate($this->getCollectionName(), []);
    }
}