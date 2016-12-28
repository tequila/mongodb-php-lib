<?php

namespace Tequila\MongoDB\Tests\Traits;

use MongoDB\Driver\Manager;

trait GetManagerTrait
{
    /**
     * @return Manager
     */
    private function getManager()
    {
        return new Manager('mongodb://127.0.0.1/');
    }
}