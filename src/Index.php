<?php

namespace Tequila\MongoDB;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class Index
{
    use CachedResolverTrait;

    /**
     * @var array
     */
    private $key;

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $key
     * @param array $options
     */
    public function __construct(array $key, array $options = [])
    {
        if (empty($key)) {
            throw new InvalidArgumentException('$key document cannot be empty');
        }

        $options = self::resolve($options);

        if (empty($options['name'])) {
            $options['name'] = self::generateIndexName($key);
        }

        $this->key = $key;
        $this->options = $options;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return ['key' => $this->key] + $this->options;
    }

    /**
     * @return array
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->options['name'];
    }

    /**
     * @param array $key
     * @return string
     */
    public static function generateIndexName(array $key)
    {
        $nameParts = [];
        foreach ($key as $fieldName => $direction) {
            $nameParts[] = $fieldName;
            $nameParts[] = (string)$direction;
        }

        return implode('_', $nameParts);
    }

    private static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'name',
            'background',
            'unique',
            'partialFilterExpression',
            'sparse',
            'expireAfterSeconds',
            'storageEngine',
            'weights',
            'default_language',
            'language_override',
            'textIndexVersion',
            '2dsphereIndexVersion',
            'bits',
            'min',
            'max',
            'bucketSize',
        ]);

        $numberTypes = ['integer', 'float'];
        $documentTypes = ['array', 'object'];

        $resolver
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('background', 'boolean')
            ->setAllowedTypes('unique', 'boolean')
            ->setAllowedTypes('partialFilterExpression', $documentTypes)
            ->setAllowedTypes('sparse', 'boolean')
            ->setAllowedTypes('expireAfterSeconds', 'integer')
            ->setAllowedTypes('storageEngine', $documentTypes)
            ->setAllowedTypes('weights', $documentTypes)
            ->setAllowedTypes('default_language', 'string')
            ->setAllowedTypes('language_override', 'string')
            ->setAllowedTypes('textIndexVersion', 'integer')
            ->setAllowedTypes('2dsphereIndexVersion', 'integer')
            ->setAllowedTypes('bits', 'integer')
            ->setAllowedTypes('min', $numberTypes)
            ->setAllowedTypes('max', $numberTypes)
            ->setAllowedTypes('bucketSize', $numberTypes);
    }
}