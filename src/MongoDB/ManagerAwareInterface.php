<?php

namespace Tequilla\MongoDB;

use MongoDB\Driver\Manager;

interface ManagerAwareInterface
{
    public function setManager(Manager $manager);
}