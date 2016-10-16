<?php

namespace Tequila\MongoDB\Traits;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverException;

trait EnsureValidUpdateTrait
{
    private static $updateResolver;

    public static function ensureValidUpdate(array $update)
    {
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