<?php

namespace Tequilla\MongoDB\Util;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverException;

final class ValidatorUtils
{
    /**
     * @var bool
     */
    private static $documentValidationEnabled = false;

    /**
     * @var OptionsResolver
     */
    private static $updateResolver;

    /**
     * @param array|object $document
     */
    public static function ensureValidDocument($document)
    {
        if (!self::$documentValidationEnabled) {
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

    public static function ensureValidFilter($filter)
    {
        if (!is_array($filter) && !is_object($filter)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$filter must be an array or an object, %s given',
                    TypeUtils::getType($filter)
                )
            );
        }
    }

    /**
     * @param array|object $update
     */
    public static function ensureValidUpdate($update)
    {
        if (!is_array($update) && !is_object($update)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$update must be an array or an object, %s given',
                    TypeUtils::getType($update)
                )
            );
        }

        $update = (array) $update;

        if (empty($update)) {
            throw new InvalidArgumentException('$update cannot be empty');
        }



        try {
            self::getUpdateResolver()->resolve($update);
        } catch(OptionsResolverException $e) {
            throw new InvalidArgumentException(
                sprintf(
                    '$update has a wrong format: %s',
                    $e->getMessage()
                )
            );
        } catch(InvalidArgumentException $e) {
            throw new InvalidArgumentException(
                sprintf(
                    '$update has a wrong format: %s',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @return OptionsResolver
     */
    private static function getUpdateResolver()
    {
        if (!self::$updateResolver) {
            $updateResolver = new OptionsResolver();
            $updateResolver->setDefined([
                '$inc',
                '$mul',
                '$rename',
                '$setOnInsert',
                '$set',
                '$unset',
                '$min',
                '$max',
                '$currentDate',
                '$bit',
            ]);

            self::$updateResolver = $updateResolver;
        }

        return self::$updateResolver;
    }

    /**
     * Enables heavy validation, like recursive documents validation etc.
     */
    public static function enableDocumentValidation()
    {
        self::$documentValidationEnabled = true;
    }

    /**
     * Disables heavy validation, like recursive documents validation etc.
     */
    public static function disableDocumentValidation()
    {
        self::$documentValidationEnabled = false;
    }
}