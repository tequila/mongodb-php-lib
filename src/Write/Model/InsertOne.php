<?php

namespace Tequilla\MongoDB\Write\Model;

use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Util\TypeUtils;
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
        if (!is_array($document) && !is_object($document)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$document must be an array or an object, %s given',
                    TypeUtils::getType($document)
                )
            );
        }

        ValidatorUtils::ensureValidDocument($document);

        $this->document = $document;
    }

    public function writeToBulk(BulkWrite $bulk)
    {
        return $bulk->insert($this->document);
    }
}