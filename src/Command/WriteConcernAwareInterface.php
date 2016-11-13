<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\WriteConcern;

interface WriteConcernAwareInterface
{
    public function setDefaultWriteConcern(WriteConcern $writeConcern);
}