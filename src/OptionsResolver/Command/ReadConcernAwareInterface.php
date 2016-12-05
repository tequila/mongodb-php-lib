<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use MongoDB\Driver\ReadConcern;

interface ReadConcernAwareInterface
{
    public function setDefaultReadConcern(ReadConcern $readConcern);
}