<?php

namespace Tequilla\MongoDB;

use MongoDB\Driver\Manager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\Driver\TypeMapOptions;

class Database implements DatabaseInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * Database constructor.
     * @param Manager $manager
     * @param string $name
     * @param array $options
     */
    public function __construct(Manager $manager, $name, array $options = [])
    {
        $this->manager = $manager;
        $this->name = $name;
        $this->options = $this->resolveOptions($options);
    }

    public function listCollections(array $options = [])
    {
        // TODO: Implement listCollections() method.
    }

    private function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'readConcern' => $this->manager->getReadConcern(),
            'readPreference' => $this->manager->getReadPreference(),
            'writeConcern' => $this->manager->getWriteConcern(),
        ]);

        TypeMapOptions::configureOptions($resolver);
        
        return $resolver->resolve($options);
    }
}