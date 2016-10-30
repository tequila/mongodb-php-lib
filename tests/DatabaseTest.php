<?php

namespace Tequila\MongoDB\Tests;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Tequila\MongoDB\Command\CreateCollection;
use Tequila\MongoDB\Command\DropDatabase;
use Tequila\MongoDB\Database;
use Tequila\MongoDB\ManagerInterface;
use Tequila\MongoDB\Tests\Traits\CursorTrait;
use Tequila\MongoDB\Tests\Traits\DatabaseAndCollectionNamesTrait;
use Tequila\MongoDB\Tests\Traits\ManagerProphecyTrait;
use Tequila\MongoDB\Tests\Traits\ServerInfoTrait;

class DatabaseTest extends TestCase
{
    use CursorTrait;
    use DatabaseAndCollectionNamesTrait;
    use ManagerProphecyTrait;
    use ServerInfoTrait;

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
        $this
            ->getManagerProphecy()
            ->executeCommand(
                $this->getDatabaseName(),
                Argument::that(function(CreateCollection $command) {
                    $expected = ['create' => $this->getCollectionName()];
                    $actual = $command->getOptions($this->getServerInfo());

                    return $expected === $actual;
                }),
                null
            )
            ->willReturn($this->getCursor())
            ->shouldBeCalled();

        $this->getDatabase()->createCollection($this->getCollectionName());
    }

    /**
     * @covers Database::drop()
     */
    public function testDropWithDefaultOptions()
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

        $this->getDatabase()->drop();
    }

    private function getDatabase()
    {
        /** @var ManagerInterface $manager */
        $manager = $this->getManagerProphecy()->reveal();

        return new Database($manager, $this->getDatabaseName());
    }
}