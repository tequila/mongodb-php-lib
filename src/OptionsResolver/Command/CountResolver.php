<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\OptionsResolver\Command\Traits\ReadConcernTrait;
use Tequila\MongoDB\Index;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadPreferenceConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\CommandOptions;

class CountResolver extends OptionsResolver implements ReadConcernAwareInterface, CompatibilityResolverInterface
{
    use ReadConcernTrait;

    public function configureOptions()
    {
        ReadConcernConfigurator::configure($this);
        ReadPreferenceConfigurator::configure($this);

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