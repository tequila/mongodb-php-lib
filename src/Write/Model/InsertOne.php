<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Util\TypeUtil;
use Tequila\MongoDB\Write\Bulk\BulkWrite;
use Tequila\MongoDB\Write\Model\Traits\EnsureValidDocumentTrait;

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
                    TypeUtil::getType($document)
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