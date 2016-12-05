<?php

namespace Tequila\MongoDB\OptionsResolver;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Exception\InvalidArgumentException;

class TypeMapResolver extends OptionsResolver
{
    public function configureOptions()
    {
        $this->setDefaults(self::getDefault());

        $this
            ->setAllowedTypes('array', 'string')
            ->setAllowedTypes('document', 'string')
            ->setAllowedTypes('root', 'string');

        $this
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
                        'Type map option "%s" must be "array", "object" or an existing class name, "%s" given',
                        $fieldName,
                        $fieldType
                    )
                );
            }

            return $fieldType;
        };
    }
}
