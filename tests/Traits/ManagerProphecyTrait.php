<?php

namespace Tequila\MongoDB\Tests\Traits;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Prophecy\Prophecy\ObjectProphecy;
use Tequila\MongoDB\Manager;

trait ManagerProphecyTrait
{
    private $managerProphecy;

    /**
     * @return ObjectProphecy
     */
    private function getManagerProphecy()
    {
        if (null === $this->managerProphecy) {
            $managerProphecy = $this->prophesize(Manager::class);

            $managerProphecy
                ->getReadConcern()
                ->willReturn(new ReadConcern());

            $managerProphecy
                ->getReadPreference()
                ->willReturn(new ReadPreference(ReadPreference::RP_PRIMARY));

            $managerProphecy
                ->getWriteConcern()
                ->willReturn(new WriteConcern(1));

            $this->managerProphecy = $managerProphecy;
        }

        return $this->managerProphecy;
    }
}