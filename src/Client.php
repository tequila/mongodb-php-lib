<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\Traits\CommandExecutorTrait;

class Client
{
    use CommandExecutorTrait;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var ReadConcern
     */
    private $readConcern;

    /**
     * @var ReadPreference
     */
    private $readPreference;

    /**
     * @var WriteConcern
     */
    private $writeConcern;

    /**
     * @param string $uri
     * @param array  $uriOptions
     * @param array  $driverOptions
     */
    public function __construct($uri = 'mongodb://127.0.0.1/', array $uriOptions = [], array $driverOptions = [])
    {
        $this->manager = new Manager($uri, $uriOptions, $driverOptions);
        $this->readConcern = $this->manager->getReadConcern();
        $this->readPreference = $this->manager->getReadPreference();
        $this->writeConcern = $this->manager->getWriteConcern();
    }

    /**
     * @param QueryListenerInterface $listener
     */
    public function addQueryListener(QueryListenerInterface $listener)
    {
        $this->manager->addQueryListener($listener);
    }

    /**
     * @param string $databaseName
     * @param array  $options
     *
     * @return array
     */
    public function dropDatabase($databaseName, array $options = [])
    {
        $cursor = $this
            ->getCommandExecutor()
            ->executeCommand(
                $this->manager,
                $databaseName,
                ['dropDatabase' => 1],
                $options
            );

        return $cursor->current();
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return array
     */
    public function listDatabases()
    {
        $cursor = $this->manager->executeCommand(
            'admin',
            new SimpleCommand(['listDatabases' => 1]),
            new ReadPreference(ReadPreference::RP_PRIMARY)
        );
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
        $result = $cursor->current();

        if (!isset($result['databases']) || !is_array($result['databases'])) {
            throw new UnexpectedResultException(
                'Command "listDatabases" did not return expected "databases" array.'
            );
        }

        return $result['databases'];
    }

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array  $options
     *
     * @return Collection
     */
    public function selectCollection($databaseName, $collectionName, array $options = [])
    {
        return new Collection($this->manager, $databaseName, $collectionName, $options);
    }

    /**
     * @param string $databaseName
     * @param $options
     *
     * @return Database
     */
    public function selectDatabase($databaseName, array $options = [])
    {
        return new Database($this->manager, $databaseName, $options);
    }
}
