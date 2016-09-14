<?php

namespace Util;

use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Util\StringUtils;
use Tequilla\MongoDB\Util\TypeUtils;

final class ValidatorUtils
{
    /**
     * @var bool
     */
    private static $validationEnabled = false;

    /**
     * Enables heavy validation, like recursive documents validation etc.
     */
    public function enableValidation()
    {
        self::$validationEnabled = true;
    }

    /**
     * Disables heavy validation, like recursive documents validation etc.
     */
    public function disableValidation()
    {
        self::$validationEnabled = false;
    }

    /**
     * @param array|object $document
     */
    public static function ensureValidDocument($document)
    {
        if (!self::$validationEnabled) {
            return;
        }

        $document = TypeUtils::ensureArrayRecursive($document);

        if (empty($document)) {
            throw new InvalidArgumentException('Document cannot be empty');
        }

        array_walk_recursive($document, function($value, $fieldName) {
            StringUtils::ensureValidDocumentFieldName($fieldName);
        });
    }
}