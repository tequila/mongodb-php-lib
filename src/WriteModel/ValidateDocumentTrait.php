<?php

namespace Tequilla\MongoDB\WriteModel;

use MongoDB\BSON\Serializable;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Util\StringUtils;
use Tequilla\MongoDB\Util\TypeUtils;

trait ValidateDocumentTrait
{
    /**
     * TODO: this is a heavy functionality. Maybe later all such traits should be moved to a separate class, where validation can be disabled
     *
     * @param array|object $document
     */
    private static function validateDocument($document)
    {
        if (!is_array($document) && !is_object($document)) {
            throw new InvalidArgumentException('$document must be an array or an object');
        }

        if ($document instanceof Serializable) {
            $document = $document->bsonSerialize();
        }

        $document = (array) $document;

        $document = TypeUtils::ensureArrayRecursive($document);

        array_walk_recursive($document, function($value, $fieldName) {
            StringUtils::ensureValidDocumentFieldName($fieldName);
        });
    }
}