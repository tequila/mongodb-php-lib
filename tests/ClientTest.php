<?php

namespace Tequila\MongoDB\Tests;

use MongoDB\Driver\WriteConcern;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Tequila\MongoDB\Client;
use Tequila\MongoDB\Command\DropDatabase;
use Tequila\MongoDB\CursorInterface;
use Tequila\MongoDB\ManagerInterface;
use Tequila\MongoDB\ServerInfo;
use Tequila\MongoDB\Tests\Traits\GetDatabaseAndCollectionNamesTrait;

class ClientTest extends TestCase
{
    use GetDatabaseAndCollectionNamesTrait;

    /**
     * @var ObjectProphecy
     */
    private $managerProphecy;

    /**
     * @var ServerInfo
     */
    private $serverInfo;

    /**
     * @var CursorInterface
     */
    private $cursor;

    public function setUp()
    {
        $this->managerProphecy = $this->prophesize(ManagerInterface::class);
        $this->serverInfo = $this->prophesize(ServerInfo::class)->reveal();
        $this->cursor = $this->prophesize(CursorInterface::class)->reveal();
    }

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
        $this->managerProphecy
            ->executeCommand(
                $this->getDatabaseName(),
                Argument::that(function(DropDatabase $command) {
                    return $command->getOptions($this->serverInfo) === ['dropDatabase' => 1];
                }),
                null
            )
            ->willReturn($this->cursor)
            ->shouldBeCalled();

        $this->getClient()->dropDatabase($this->getDatabaseName());
    }

    /**
     * @covers Client::dropDatabase()
     */
    public function testDropDatabaseWithWriteConcern()
    {
        $this->managerProphecy
            ->executeCommand(
                $this->getDatabaseName(),
                Argument::that(function(DropDatabase $command) {
                    $options = $command->getOptions($this->serverInfo);

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
            ->willReturn($this->cursor)
            ->shouldBeCalled();

        $this->getClient()->dropDatabase(
            $this->getDatabaseName(),
            ['writeConcern' => new WriteConcern(WriteConcern::MAJORITY, 1000)]
        );
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        /** @var ManagerInterface $manager */
        $manager = $this->managerProphecy->reveal();

        return new Client($manager);
    }
}