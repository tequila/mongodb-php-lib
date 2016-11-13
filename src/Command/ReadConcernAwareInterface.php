<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\ReadConcern;

interface ReadConcernAwareInterface
{
    public function setDefaultReadConcern(ReadConcern $readConcern);
}