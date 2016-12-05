<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\OptionsResolver\Command\Traits\ReadConcernTrait;
use Tequila\MongoDB\OptionsResolver\Command\Traits\ReadPreferenceTrait;
use Tequila\MongoDB\Index;
use Tequila\MongoDB\OptionsResolver\Command\CompatibilityResolverInterface;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\CommandOptions;

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
     * @param CommandOptions $options
     */
    public function resolveCompatibilities(CommandOptions $options)
    {
        $options->resolveReadConcern($this->readConcern);
    }
}