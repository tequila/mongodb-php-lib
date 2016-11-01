<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Options\CollationOptions;
use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Traits\CachedResolverTrait;
use Tequila\MongoDB\ServerInfo;

class FindOneAndDelete implements CommandInterface
{
    use CachedResolverTrait;
    use PrimaryServerTrait;

    /**
     * @var FindAndModify
     */
    private $findAndModify;

    /**
     * @param string $collectionName
     * @param array $filter
     * @param array $options
     */
    public function __construct($collectionName, array $filter, array $options = [])
    {
        $options = ['remove' => true] + self::resolve($options);
        if (isset($options['projection'])) {
            $options['fields'] = $options['projection'];
            unset($options['projection']);
        }

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
            'maxTimeMS',
            'projection',
            'sort',
        ]);
    }
}