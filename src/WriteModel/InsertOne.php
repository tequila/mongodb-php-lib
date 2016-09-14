<?php

namespace WriteModel;

use MongoDB\Driver\BulkWrite;
use Tequilla\MongoDB\WriteModel\WriteModelInterface;
use Util\ValidatorUtils;

class InsertOne implements WriteModelInterface
{
    /**
     * @var array|object
     */
    private $document;

    /**
     * @param array|object $document
     */
    public function __construct($document)
    {
        ValidatorUtils::ensureValidDocument($document);

        $this->document = $document;
    }

    public function writeToBulk(BulkWrite $bulk)
    {
        return $bulk->insert($this->document);
    }
}