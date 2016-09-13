<?php

namespace Tequilla\MongoDB\Util;

use Tequilla\MongoDB\Exception\InvalidArgumentException;

final class StringUtils
{
    /**
     * Checks if string starts with substring
     *
     * @param $string
     * @param $substring
     * @return bool
     */
    public static function startsWith($string, $substring)
    {
        return 0 === mb_strpos($string, $substring);
    }

    /**
     * Ensures that given string is a valid MongoDB database name
     *
     * @param string $databaseName
     * @throws InvalidArgumentException
     */
    public static function ensureValidDatabaseName($databaseName) {
        if (!is_string($databaseName)) {
            throw new InvalidArgumentException(
                sprintf('Database name must be a string, %s given', TypeUtils::getType($databaseName))
            );
        }

        if (empty($databaseName)) {
            throw new InvalidArgumentException('Database name cannot be empty.');
        }
    }

    /**
     * Ensures that given string is a valid MongoDB collection name
     *
     * @param string $collectionName
     * @throws InvalidArgumentException
     */
    public static function ensureValidCollectionName($collectionName) {
        if (!is_string($collectionName)) {
            throw new InvalidArgumentException(
                sprintf('Collection name must be a string, %s given', TypeUtils::getType($collectionName))
            );
        }

        if (empty($collectionName)) {
            throw new InvalidArgumentException('Collection name cannot be empty.');
        }
    }

    /**
     * Ensures that given string is a valid MongoDB document field name
     *
     * @param string $fieldName
     */
    public static function ensureValidDocumentFieldName($fieldName) {
        if (!is_string($fieldName)) {
            throw new InvalidArgumentException(
                sprintf('Document\'s field name must be a string, %s given', TypeUtils::getType($fieldName))
            );
        }

        if (empty($fieldName)) {
            throw new InvalidArgumentException('Document\'s field name cannot be empty.');
        }

        if (self::startsWith($fieldName, '$')) {
            throw new InvalidArgumentException(
                sprintf(
                    'Document\'s field name cannot start with "$" character, field name "%s" given',
                    $fieldName
                )
            );
        }
    }

    public static function createNamespace($databaseName, $collectionName)
    {
        self::ensureValidDatabaseName($databaseName);
        self::ensureValidCollectionName($collectionName);

        return $databaseName . '.' . $collectionName;
    }
}