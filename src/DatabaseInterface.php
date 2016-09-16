<?php

namespace Tequilla\MongoDB;

interface DatabaseInterface
{
    /**
     * @param  [type] $name
     * @param  [type] $options
     * @return [type]
     */
    public function createCollection($collectionName, array $options = []);

    public function getName();

    /**
     * @param $collectionName
     * @return CollectionInterface
     */
    public function selectCollection($collectionName);

    public function drop(array $options = []);

    public function listCollections(array $options = []);
}
