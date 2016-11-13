<?php

namespace Tequila\MongoDB\Command\Traits;

use MongoDB\Driver\WriteConcern;

trait WriteConcernTrait
{
    /**
     * @var WriteConcern
     */
    private $writeConcern;

    /**
     * @param WriteConcern $writeConcern
     * @return $this
     */
    public function setDefaultWriteConcern(WriteConcern $writeConcern)
    {
        $this->writeConcern = $writeConcern;

        return $this;
    }
}