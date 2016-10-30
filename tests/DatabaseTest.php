<?php

namespace Tequila\MongoDB\Tests;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Tequila\MongoDB\Command\CreateCollection;
use Tequila\MongoDB\CursorInterface;
use Tequila\MongoDB\Database;
use Tequila\MongoDB\ManagerInterface;
use Tequila\MongoDB\ServerInfo;
use Tequila\MongoDB\Tests\Traits\GetDatabaseAndCollectionNamesTrait;

class DatabaseTest extends TestCase
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
     * @covers Database::__construct()
     */
    public function testConstructorWithDefaultOptions()
    {
        $this->getDatabase();
    }

    /**
     * @covers Database::createCollection()
     */
    public function testCreateCollectionWithDefaultOptions()
    {
        $this->managerStubReturnsReadPreferenceAndConcerns();
        $this->managerProphecy
            ->executeCommand(
                $this->getDatabaseName(),
                Argument::that(function(CreateCollection $command) {
                    $expected = ['create' => $this->getCollectionName()];
                    $actual = $command->getOptions($this->serverInfo);

                    return $expected === $actual;
                }),
                null
            )
            ->willReturn($this->cursor)
            ->shouldBeCalled();

        $this->getDatabase()->createCollection($this->getCollectionName());
    }

    private function getDatabase()
    {
        $this->managerStubReturnsReadPreferenceAndConcerns();
        /** @var ManagerInterface $manager */
        $manager = $this->managerProphecy->reveal();

        return new Database($manager, $this->getDatabaseName());
    }

    private function managerStubReturnsReadPreferenceAndConcerns()
    {
        $this->managerProphecy
            ->getReadConcern()
            ->willReturn(new ReadConcern());

        $this->managerProphecy
            ->getReadPreference()
            ->willReturn(new ReadPreference(ReadPreference::RP_PRIMARY));

        $this->managerProphecy
            ->getWriteConcern()
            ->willReturn(new WriteConcern(1));
    }
}