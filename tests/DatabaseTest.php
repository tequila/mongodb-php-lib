<?php

namespace Tequila\MongoDB\Tests;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\OptionsResolver\Command\CreateCollectionResolver;
use Tequila\MongoDB\OptionsResolver\Command\DropCollectionResolver;
use Tequila\MongoDB\OptionsResolver\Command\DropDatabaseResolver;
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
                Argument::that(function(CreateCollectionResolver $command) {
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
                Argument::that(function(DropDatabaseResolver $command) {
                    return ['dropDatabase' => 1] === $command->getOptions($this->getServerInfo());
                }),
                null
            )
            ->willReturn($this->getCursor())
            ->shouldBeCalled();

        $this->getDatabase()->drop();
    }

    /**
     * @covers Database::dropCollection()
     */
    public function testDropCollectionWithDefaultOptions()
    {
        $this
            ->getManagerProphecy()
            ->executeCommand(
                $this->getDatabaseName(),
                Argument::that(function(DropCollectionResolver $command) {
                    $actual = $command->getOptions($this->getServerInfo());

                    return ['drop' => $this->getCollectionName()] === $actual;
                }),
                null
            )
            ->willReturn($this->getCursor())
            ->shouldBeCalled();

        $this->getDatabase()->dropCollection($this->getCollectionName());
    }

    /**
     * @covers Database::selectCollection()
     */
    public function testSelectCollectionWithDefaultOptions()
    {
        $collection = $this->getDatabase()->selectCollection($this->getCollectionName());
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals($this->getDatabaseName(), $collection->getDatabaseName());
        $this->assertEquals($this->getCollectionName(), $collection->getCollectionName());
    }

    private function getDatabase()
    {
        /** @var ManagerInterface $manager */
        $manager = $this->getManagerProphecy()->reveal();

        return new Database($manager, $this->getDatabaseName());
    }
}