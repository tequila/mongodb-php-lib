<?php

namespace Tequila\MongoDB\Command\Options;

use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class FindOneAndDeleteOptions implements OptionsInterface
{
    use CachedResolverTrait {
        CachedResolverTrait::resolve as resolveOptions;
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'maxTimeMS',
            'projection',
            'sort',
            'collation',
        ]);
    }

    /**
     * Translates FindOneAndDelete options to FindAndModify options
     *
     * @param array $options
     * @return array
     */
    public static function resolve(array $options = [])
    {
        $options = self::resolveOptions($options);
        $options['remove'] = true;
        if (isset($options['projection'])) {
            $options['fields'] = $options['projection'];
            unset($options['projection']);
        }

        return $options;
    }
}