<?php

namespace Tequila\MongoDB\Tests;

use MongoDB\Driver\WriteConcern;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Tequila\MongoDB\Client;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Command\DropDatabase;
use Tequila\MongoDB\Database;
use Tequila\MongoDB\ManagerInterface;
use Tequila\MongoDB\Tests\Traits\CursorTrait;
use Tequila\MongoDB\Tests\Traits\DatabaseAndCollectionNamesTrait;
use Tequila\MongoDB\Tests\Traits\ManagerProphecyTrait;
use Tequila\MongoDB\Tests\Traits\ServerInfoTrait;

class ClientTest extends TestCase
{
    use CursorTrait;
    use DatabaseAndCollectionNamesTrait;
    use ManagerProphecyTrait;
    use ServerInfoTrait;

    /**
     * @covers Client::__construct()
     */
    public function testConstructorWithManagerInterface()
    {
        $this->getClient();
    }

    /**
     * @covers Client::dropDatabase()
     */
    public function testDropDatabaseWithDefaultOptions()
    {
        $this
            ->getManagerProphecy()
            ->executeCommand(
                $this->getDatabaseName(),
                Argument::that(function(DropDatabase $command) {
                    return ['dropDatabase' => 1] === $command->getOptions($this->getServerInfo());
                }),
                null
            )
            ->willReturn($this->getCursor())
            ->shouldBeCalled();

        $this->getClient()->dropDatabase($this->getDatabaseName());
    }

    /**
     * @covers Client::selectCollection()
     */
    public function testSelectCollectionWithDefaultOptions()
    {
        $client = $this->getClient();
        $collection = $client->selectCollection($this->getDatabaseName(), $this->getCollectionName());
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals($this->getDatabaseName(), $collection->getDatabaseName());
        $this->assertEquals($this->getCollectionName(), $collection->getCollectionName());
    }

    /**
     * @covers Client::selectCollection()
     */
    public function testSelectDatabaseWithDefaultOptions()
    {
        $client = $this->getClient();
        $database = $client->selectDatabase($this->getDatabaseName());
        $this->assertInstanceOf(Database::class, $database);
        $this->assertEquals($this->getDatabaseName(), $database->getDatabaseName());
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        /** @var ManagerInterface $manager */
        $manager = $this->getManagerProphecy()->reveal();

        return new Client($manager);
    }
}