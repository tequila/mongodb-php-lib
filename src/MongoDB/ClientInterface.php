<?php

namespace Tequilla\MongoDB;

/**
 * Interface ClientInterface
 * @package Tequilla\MongoDB
 */
interface ClientInterface
{
    /**
     * @param string $databaseName
     * @param array $options
     * @return mixed
     */
    public function dropDatabase($databaseName, array $options);

    /**
     * @param array $options
     * @return mixed
     */
    public function listDatabase(array $options);

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array $options
     * @return CollectionInterface
     */
    public function selectCollection($databaseName, $collectionName, array $options);

    /**
     * @param string $databaseName
     * @param array $options
     * @return DatabaseInterface
     */
    public function selectDatabase($databaseName, array $options);
}