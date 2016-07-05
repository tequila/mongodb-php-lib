<?php

namespace Tequilla\MongoDB;

/**
 * Interface ClientInterface
 * @package Tequilla\MongoDB
 */
interface ClientInterface
{
    /**
     * @param array $options
     * @return string[]
     */
    public function listDatabases(array $options);

    /**
     * @param string $databaseName
     * @param array $options
     * @return DatabaseInterface
     */
    public function selectDatabase($databaseName, array $options = []);
}