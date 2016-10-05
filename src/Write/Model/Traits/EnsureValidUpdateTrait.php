<?php

namespace Tequila\MongoDB\Write\Model\Traits;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Util\TypeUtil;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverException;

trait EnsureValidUpdateTrait
{
    private static $updateResolver;

    public static function ensureValidUpdate($update)
    {
        if (!is_array($update) && !is_object($update)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$update must be an array or an object, %s given',
                    TypeUtil::getType($update)
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
}