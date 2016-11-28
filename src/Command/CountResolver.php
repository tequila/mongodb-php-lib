<?php

namespace Tequila\MongoDB\Command;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Command\Traits\ReadConcernTrait;
use Tequila\MongoDB\Command\Traits\ReadPreferenceTrait;
use Tequila\MongoDB\Index;
use Tequila\MongoDB\Options\CompatibilityResolverInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\ServerCompatibleOptions;

class CountResolver extends OptionsResolver implements
    ReadConcernAwareInterface,
    ReadPreferenceResolverInterface,
    CompatibilityResolverInterface
{
    use ReadConcernTrait;
    use ReadPreferenceTrait;

    public function configureOptions()
    {
        $this->setDefined([
            'limit',
            'skip',
            'hint',
        ]);

        $this
            ->setAllowedTypes('limit', 'integer')
            ->setAllowedTypes('skip', 'integer')
            ->setAllowedTypes('hint', ['string', 'array', 'object']);

        $this->setNormalizer('hint', function(Options $options, $hint) {
            if (is_array($hint) || is_object($hint)) {
                $hint = Index::generateIndexName((array)$hint);
            }

            return $hint;
        });
    }

    /**
     * @param ServerCompatibleOptions $options
     */
    public function resolveCompatibilities(ServerCompatibleOptions $options)
    {
        $options->resolveReadConcern($this->readConcern);
    }
}