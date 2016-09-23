<?php

namespace Tequila\MongoDB\Options\Driver;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\ConfigurableInterface;
use MongoDB\Driver\Cursor;

class TypeMapOptions implements ConfigurableInterface
{
    const TYPE_MAP = 'typeMap';

    public static function getAll()
    {
        return [ self::TYPE_MAP ];
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(self::getAll());
        $resolver->setAllowedTypes(self::TYPE_MAP, 'array');
        $resolver->setDefault(self::TYPE_MAP, [
            'array' => 'array',
            'document' => 'array',
            'root' => 'array',
        ]);
        $resolver->setNormalizer(self::TYPE_MAP, function(Options $options, $value) {
            $typeMapResolver = new OptionsResolver();
            $typeMapResolver->setDefined([
                'array',
                'document',
                'root',
            ]);

            return $typeMapResolver->resolve($value);
        });
    }

    public static function setArrayTypeMapOnCursor(Cursor $cursor)
    {
        $cursor->setTypeMap([
            'root' => 'array',
            'document' => 'array',
            'array' => 'array',
        ]);

        return $cursor;
    }
}
