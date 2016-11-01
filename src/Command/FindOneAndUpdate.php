<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Options\CollationOptions;
use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Traits\CachedResolverTrait;
use Tequila\MongoDB\ServerInfo;

class FindOneAndUpdate implements CommandInterface
{
    use CachedResolverTrait;
    use PrimaryServerTrait;

    const RETURN_DOCUMENT_BEFORE = 'before';
    const RETURN_DOCUMENT_AFTER = 'after';

    /**
     * @var FindAndModify
     */
    private $findAndModify;

    /**
     * @param string $collectionName
     * @param array $filter
     * @param $update
     * @param array $options
     */
    public function __construct($collectionName, array $filter, $update, array $options = [])
    {
        $options = ['update' => $update] + self::resolve($options);
        if (isset($options['projection'])) {
            $options['fields'] = $options['projection'];
            unset($options['projection']);
        }

        if (self::RETURN_DOCUMENT_AFTER === $options['returnDocument']) {
            $options['new'] = true;
        }

        unset($options['returnDocument']);

        $this->findAndModify = new FindAndModify(
            $collectionName,
            $filter,
            $options
        );
    }

    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        return $this->findAndModify->getOptions($serverInfo);
    }

    private static function configureOptions(OptionsResolver $resolver)
    {
        CollationOptions::configureOptions($resolver);
        WritingCommandOptions::configureOptions($resolver);

        $resolver->setDefined([
            'bypassDocumentValidation',
            'maxTimeMS',
            'projection',
            'returnDocument',
            'sort',
            'upsert',
        ]);

        $resolver->setAllowedValues(
            'returnDocument',
            [
                self::RETURN_DOCUMENT_BEFORE,
                self::RETURN_DOCUMENT_AFTER,
            ]
        );

        $resolver->setDefault('returnDocument', self::RETURN_DOCUMENT_BEFORE);
    }
}