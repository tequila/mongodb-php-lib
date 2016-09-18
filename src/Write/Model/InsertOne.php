<?php

namespace Tequilla\MongoDB\Write\Model;

use Tequilla\MongoDB\Write\Bulk\BulkWrite;
use Tequilla\MongoDB\Util\ValidatorUtils;

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