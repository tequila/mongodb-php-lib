<?php

namespace Tequila\MongoDB\OptionsResolver\Command\Traits;

use MongoDB\Driver\ReadConcern;

trait ReadConcernTrait
{
    /**
     * @var ReadConcern
     */
    private $readConcern;

    /**
     * @param ReadConcern $readConcern
     * @return $this
     */
    public function setDefaultReadConcern(ReadConcern $readConcern)
    {
        $this->readConcern = $readConcern;

        return $this;
    }
}