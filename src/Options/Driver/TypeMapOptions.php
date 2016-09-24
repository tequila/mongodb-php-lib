<?php

namespace Tequila\MongoDB\Options\Driver;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\BSON\BSONArray;
use Tequila\MongoDB\BSON\BSONDocument;
use Tequila\MongoDB\Options\ConfigurableInterface;

class TypeMapOptions implements ConfigurableInterface
{
    const TYPE_MAP = 'typeMap';

    /**
     * @var OptionsResolver
     */
    private static $typeMapResolver;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(self::TYPE_MAP);
        $resolver->setAllowedTypes(self::TYPE_MAP, 'array');
        $resolver->setDefault(self::TYPE_MAP, [
            'array' => BSONArray::class,
            'document' => BSONDocument::class,
            'root' => BSONDocument::class,
        ]);
        $resolver->setNormalizer(self::TYPE_MAP, function(Options $options, $value) {
            return self::getTypeMapResolver()->resolve($value);
        });
    }

    private static function getTypeMapResolver()
    {
        if (null === self::$typeMapResolver) {
            $resolver = new OptionsResolver();
            $resolver->setDefined([
                'array',
                'document',
                'root',
            ]);

            self::$typeMapResolver = $resolver;
        }

        return self::$typeMapResolver;
    }
}
