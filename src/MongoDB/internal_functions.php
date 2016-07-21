<?php

namespace Tequilla\MongoDB;

use Tequilla\MongoDB\Exception\InvalidArgumentException;

/**
 * This function is intended for us in OptionsResolver,
 * it gives the ability to call OptionsResolver::setAllowedTypes($options, 'list');
 *
 * @param $value
 * @return bool
 */
function isList($value) {
    if (!is_array($value)) {
        return false;
    }

    return array_keys($value) === range(0, count($value) - 1);
}

/**
 * @param mixed $value
 * @return string
 */
function getType($value) {
    return is_object($value) ? get_class($value) : \gettype($value);
}

/**
 * @param string $name
 */
function ensureValidDatabaseName($name) {
    if (!is_string($name)) {
        throw new InvalidArgumentException(
            sprintf('Database name must be a string, %s given', getType($name))
        );
    }

    if (empty($name)) {
        throw new InvalidArgumentException('Database name cannot be empty.');
    }
}

/**
 * @param string $name
 */
function ensureValidCollectionName($name) {
    if (!is_string($name)) {
        throw new InvalidArgumentException(
            sprintf('Collection name must be a string, %s given', getType($name))
        );
    }

    if (empty($name)) {
        throw new InvalidArgumentException('Collection name cannot be empty.');
    }
}

/**
 * @param string $name
 */
function ensureValidDocumentFieldName($name) {
    if (!is_string($name)) {
        throw new InvalidArgumentException(
            sprintf('Document\'s field name must be a string, %s given', getType($name))
        );
    }

    if (empty($name)) {
        throw new InvalidArgumentException('Document\'s field name cannot be empty.');
    }
}

/**
 * @param string $namespace
 */
function ensureValidNamespace($namespace) {
    if (!is_string($namespace)) {
        throw new InvalidArgumentException(
            sprintf('Namespace must be a string, %s given', getType($namespace))
        );
    }

    if (empty($namespace)) {
        throw new InvalidArgumentException('Namespace cannot be empty.');
    }

    $parts = explode('.', $namespace);
    if (count($parts) < 2) {
        throw new InvalidArgumentException(
            'Namespace must contain database name and collection name, separated by a dot.'
        );
    }

    list ($databaseName, $collectionName) = $parts;
    ensureValidDatabaseName($databaseName);
    ensureValidCollectionName($collectionName);
}

/**
 * @param string $className
 */
function ensureClassExists($className) {
    if (!is_string($className)) {
        throw new InvalidArgumentException(
            sprintf('Class name must be a string, %s given', getType($className))
        );
    }

    if (!class_exists($className)) {
        throw new \InvalidArgumentException(
            sprintf('Class "%s" is not found', $className)
        );
    }
}

/**
 * @param string $className
 * @param string $parentName
 */
function ensureIsSubclassOf($className, $parentName) {
    ensureClassExists($className);

    if (!is_subclass_of($className, $parentName)) {
        throw new InvalidArgumentException(
            sprintf(
                'Only classes, which implement "%s" are allowed, %s given',
                $parentName,
                $className
            )
        );
    }
}