<?php

namespace Tequilla\MongoDB\Write\Model;

use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Util\TypeUtils;
use Tequilla\MongoDB\Write\Bulk\BulkWrite;
use Tequilla\MongoDB\Write\Model\Traits\EnsureValidDocumentTrait;

class InsertOne implements WriteModelInterface
{
    use EnsureValidDocumentTrait;

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

        $this->ensureValidDocument($document);

        $this->document = $document;
    }

    public function writeToBulk(BulkWrite $bulk)
    {
        return $bulk->insert($this->document);
    }
}