<?php

namespace Tequila\MongoDB\Command\Traits;

trait PrimaryServerTrait
{
    public function needsPrimaryServer()
    {
        return true;
    }
}