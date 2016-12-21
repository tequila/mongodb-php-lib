<?php

namespace Tequila\MongoDB;

use MongoDB\BSON\Serializable;
use Tequila\MongoDB\Exception\InvalidArgumentException;

function ensureValidDocument($document) {
    if ($document instanceof Serializable) {
        $document = $document->bsonSerialize();
    }

    $document = (array) $document;

    foreach ($document as $fieldName => $value) {
        if (!preg_match('/^[^$][^\.]*$/', $fieldName)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid field name "%s": field names cannot start with a dollar sign ("$") and cannot contain dots.',
                    $fieldName
                )
            );
        }

        if (is_array($value) || is_object($value)) {
            ensureValidDocument($document);
        }
    }
}

function getType($value) {
    return is_object($value) ? get_class($value) : \gettype($value);
}