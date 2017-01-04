<?php

namespace Tequila\MongoDB\OptionsResolver;

use Tequila\MongoDB\Exception\InvalidArgumentException;

class TypeMapResolver extends OptionsResolver
{
    protected function configureOptions()
    {
        $this->setDefaults([
            'root' => 'array',
            'document' => 'array',
            'array' => 'array',
        ]);

        $this
            ->setAllowedTypes('array', 'string')
            ->setAllowedTypes('document', 'string')
            ->setAllowedTypes('root', 'string');

        $normalizer = function($value, $option) {
            if (!in_array($value, ['array', 'object'], true) && !class_exists($value)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Type map option "%s" must be "array", "object" or an existing class name, "%s" given.',
                        $option,
                        $value
                    )
                );
            }

            return $value;
        };

        $this
            ->setNormalizer('array', $normalizer)
            ->setNormalizer('document', $normalizer)
            ->setNormalizer('root', $normalizer);
    }
}
