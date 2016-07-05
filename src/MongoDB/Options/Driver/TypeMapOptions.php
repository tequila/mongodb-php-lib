<?php

namespace Tequilla\MongoDB\Options\Driver;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableClassInterface;

class TypeMapOptions implements ConfigurableClassInterface
{
    const TYPE_MAP = 'typeMap';

    public static function getAll()
    {
        return [ self::TYPE_MAP ];
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(self::getAll());
        $resolver->setAllowedTypes(self::TYPE_MAP, ['array']);
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
}