<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use MongoDB\Driver\WriteConcern;

interface WriteConcernAwareInterface
{
    public function setDefaultWriteConcern(WriteConcern $writeConcern);
}