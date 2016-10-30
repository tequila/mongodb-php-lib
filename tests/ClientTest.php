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
                    return $command->getOptions($this->getServerInfo()) === ['dropDatabase' => 1];
                }),
                null
            )
            ->willReturn($this->getCursor())
            ->shouldBeCalled();

        $this->getClient()->dropDatabase($this->getDatabaseName());
    }

    /**
     * @covers Client::dropDatabase()
     */
    public function testDropDatabaseWithWriteConcern()
    {
        $this
            ->getManagerProphecy()
            ->executeCommand(
                $this->getDatabaseName(),
                Argument::that(function(DropDatabase $command) {
                    $options = $command->getOptions($this->getServerInfo());

                    if (2 !== count($options)) {
                        return false;
                    }

                    if ('dropDatabase' !== key($options) || 1 !== current($options)) {
                        return false;
                    }

                    if (!isset($options['writeConcern'])) {
                        return false;
                    }

                    $writeConcern = (array)$options['writeConcern'];
                    $expectedWriteConcern = [
                        'w' => WriteConcern::MAJORITY,
                        'wtimeout' => 1000,
                    ];

                    return $writeConcern === $expectedWriteConcern;
                }),
                null
            )
            ->willReturn($this->getCursor())
            ->shouldBeCalled();

        $this->getClient()->dropDatabase(
            $this->getDatabaseName(),
            ['writeConcern' => new WriteConcern(WriteConcern::MAJORITY, 1000)]
        );
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