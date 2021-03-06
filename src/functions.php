<?php

namespace Tequila\MongoDB;

use MongoDB\BSON\Serializable;
use Tequila\MongoDB\Exception\InvalidArgumentException;

/**
 * @param $document
 * @param array $typeMap
 *
 * @return object
 */
function applyTypeMap($document, array $typeMap)
{
    return \MongoDB\BSON\toPHP(\MongoDB\BSON\fromPHP($document), $typeMap);
}

function ensureValidDocument($document)
{
    if ($document instanceof Serializable) {
        // do not validate Serializable instances since call to Serializable::bsonSerialize()
        // will increase a memory usage by creating array|object copy of the document
        return;
    }

    $arrayDocument = (array) $document;
    $firstFieldName = key($arrayDocument);
    if (!preg_match('/^[^$][^\.]*$/', $firstFieldName)) {
        throw new InvalidArgumentException(
            sprintf(
                'Invalid field name "%s": field names cannot start with a dollar sign ("$") and cannot contain dots.',
                $firstFieldName
            )
        );
    }
}

function ensureValidUpdate(array $update)
{
    $firstOperator = key($update);

    if ('$' !== substr($firstOperator, 0, 1)) {
        throw new InvalidArgumentException(
            sprintf(
                'Invalid $update document: first key "%s" is not an update operator.',
                $firstOperator
            )
        );
    }
}

function getType($value)
{
    return is_object($value) ? get_class($value) : \gettype($value);
}

/**
 * @param string $value - a value to check
 *
 * @return bool
 */
function isValidObjectId($value)
{
    return 24 === strspn((string) $value, '0123456789ABCDEFabcdef');
}
