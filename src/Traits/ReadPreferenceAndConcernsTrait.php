<?php

namespace Tequilla\MongoDB\Traits;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequilla\MongoDB\Exception\LogicException;

trait ReadPreferenceAndConcernsTrait
{
    /**
     * @var ReadConcern
     */
    private $readConcern;

    /**
     * @var ReadPreference
     */
    private $readPreference;

    /**
     * @var WriteConcern
     */
    private $writeConcern;

    /**
     * @return ReadConcern
     * @throws LogicException
     */
    public function getReadConcern()
    {
        if (!$this->readConcern) {
            throw new LogicException('$readConcern is not set on this instance');
        }

        return $this->readConcern;
    }

    /**
     * @param ReadConcern $readConcern
     * @return $this
     */
    public function setReadConcern(ReadConcern $readConcern)
    {
        $this->readConcern = $readConcern;

        return $this;
    }

    /**
     * @return ReadPreference
     * @throws LogicException
     */
    public function getReadPreference()
    {
        if (!$this->readPreference) {
            throw new LogicException('$readPreference is not set on this instance');
        }

        return $this->readPreference;
    }

    /**
     * @param ReadPreference $readPreference
     * @return $this
     */
    public function setReadPreference(ReadPreference $readPreference)
    {
        $this->readPreference = $readPreference;

        return $this;
    }

    /**
     * @return WriteConcern
     * @throws LogicException
     */
    public function getWriteConcern()
    {
        if (!$this->writeConcern) {
            throw new LogicException('$writeConcern is not set on this instance');
        }

        return $this->writeConcern;
    }

    /**
     * @param WriteConcern $writeConcern
     * @return $this
     */
    public function setWriteConcern(WriteConcern $writeConcern)
    {
        $this->writeConcern = $writeConcern;

        return $this;
    }
}