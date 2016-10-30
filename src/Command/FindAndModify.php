<?php

namespace Tequila\MongoDB\Command;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Traits\CachedResolverTrait;
use Tequila\MongoDB\ServerInfo;

class FindAndModify implements CommandInterface
{
    use CachedResolverTrait;
    use PrimaryServerTrait;

    const RETURN_DOCUMENT_BEFORE = 'before';
    const RETURN_DOCUMENT_AFTER = 'after';

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $collectionName
     * @param array $query
     * @param array $options
     */
    public function __construct($collectionName, array $query, array $options)
    {
        $this->options = [
                'findAndModify' => $collectionName,
                'query' => $query
            ] + self::resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        return $this->options;
    }

    private static function configureOptions(OptionsResolver $resolver)
    {
        WritingCommandOptions::configureOptions($resolver);

        $resolver->setDefined([
            'sort',
            'remove',
            'update',
            'new',
            'fields',
            'upsert',
            'bypassDocumentValidation',
            'maxTimeMS',
        ]);

        $documentTypes = ['array', 'object'];

        $resolver
            ->setAllowedTypes('sort', $documentTypes)
            ->setAllowedTypes('remove', 'bool')
            ->setAllowedTypes('update', $documentTypes)
            ->setAllowedTypes('new', 'bool')
            ->setAllowedTypes('fields', $documentTypes)
            ->setAllowedTypes('upsert', 'bool')
            ->setAllowedTypes('bypassDocumentValidation', 'bool')
            ->setAllowedTypes('maxTimeMS', 'integer');

        $resolver->setDefault('remove', false);

        $resolver->setNormalizer('remove', function (Options $options, $remove) {
            if (true === $remove) {
                foreach (['update', 'new', 'upsert'] as $prohibitedOption) {
                    if (isset($options[$prohibitedOption])) {
                        throw new InvalidArgumentException(
                            sprintf(
                                'Option "%s" is not allowed when option "remove" is set to true',
                                $prohibitedOption
                            )
                        );
                    }
                }
            } else {
                if (!isset($options['update'])) {
                    throw new InvalidArgumentException(
                        'Option "update" is required when option "remove" is set to false or not exists'
                    );
                }
            }
        });
    }
}