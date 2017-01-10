<?php

namespace Tequila\MongoDB;

use MongoDB\BSON\Serializable;
use Tequila\MongoDB\Exception\InvalidArgumentException;

function ensureValidDocument($document) {
    if ($document instanceof Serializable) {
        // do not validate Serializable instances since call to Serializable::bsonSerialize()
        // will increase a memory usage by creating array|object copy of the document
        return;
    }

    $arrayDocument = (array) $document;

    foreach ($arrayDocument as $fieldName => $value) {
        if (!preg_match('/^[^$][^\.]*$/', $fieldName)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid field name "%s": field names cannot start with a dollar sign ("$") and cannot contain dots.',
                    $fieldName
                )
            );
        }
    }
}

function getType($value) {
    return is_object($value) ? get_class($value) : \gettype($value);
}

/**
 * @param string $value - a value to check
 * @return bool
 */
function isValidObjectId($value) {
    return 24 === strspn((string)$value,'0123456789ABCDEFabcdef');
}