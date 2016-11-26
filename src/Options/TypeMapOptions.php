<?php

namespace Tequila\MongoDB\Options;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class TypeMapOptions
{
    use CachedResolverTrait {
        resolve as privateResolve;
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(self::getDefault());

        $resolver
            ->setAllowedTypes('array', 'string')
            ->setAllowedTypes('document', 'string')
            ->setAllowedTypes('root', 'string');

        $resolver
            ->setNormalizer('array', self::getNormalizer('array'))
            ->setNormalizer('document', self::getNormalizer('document'))
            ->setNormalizer('root', self::getNormalizer('root'));
    }

    public static function getDefault()
    {
        return [
            'root' => 'array',
            'document' => 'array',
            'array' => 'array',
        ];
    }

    public static function resolve(array $options)
    {
        return self::privateResolve($options);
    }

    /**
     * @param string $fieldName
     * @return \Closure
     */
    private static function getNormalizer($fieldName)
    {
        return function(Options $options, $fieldType) use($fieldName) {
            if (!in_array($fieldType, ['array', 'object'], true) && !class_exists($fieldType)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Type map option "%s" must be "array", "object" or a class name, "%s" given',
                        $fieldName,
                        $fieldType
                    )
                );
            }

            return $fieldType;
        };
    }
}
