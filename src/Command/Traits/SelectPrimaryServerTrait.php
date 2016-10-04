<?php

namespace Tequila\MongoDB\Command\Traits;

use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadPreference;

trait SelectPrimaryServerTrait
{
    /**
     * @param Manager $manager
     * @return \MongoDB\Driver\Server
     */
    private function selectPrimaryServer(Manager $manager)
    {
        return $manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));
    }
}