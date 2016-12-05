<?php

namespace Tequila\MongoDB\Write\Result;

use Tequila\MongoDB\WriteResult;

trait WriteResultDecoratorTrait
{
    /**
     * @var WriteResult
     */
    private $writeResult;

    /**
     * @param WriteResult $writeResult
     */
    public function __construct(WriteResult $writeResult)
    {
        $this->writeResult = $writeResult;
    }

    /**
     * @return bool
     */
    public function isAcknowledged()
    {
        return $this->writeResult->isAcknowledged();
    }

    /**
     * @return \MongoDB\Driver\WriteError[]
     */
    public function getWriteErrors()
    {
        return $this->writeResult->getWriteErrors();
    }

    /**
     * @return \MongoDB\Driver\WriteConcernError|null
     */
    public function getWriteConcernError()
    {
        return $this->writeResult->getWriteConcernError();
    }
}