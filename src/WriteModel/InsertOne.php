<?php

namespace WriteModel;

use MongoDB\Driver\BulkWrite;
use Tequilla\MongoDB\WriteModel\ValidateDocumentTrait;
use Tequilla\MongoDB\WriteModel\WriteModelInterface;

class InsertOne implements WriteModelInterface
{
    use ValidateDocumentTrait;
    /**
     * @var array|object
     */
    private $document;

    public function __construct($document)
    {
        self::validateDocument($document);

        $this->document = $document;
    }

    public function writeToBulk(BulkWrite $bulk)
    {
        return $bulk->insert($this->document);
    }
}