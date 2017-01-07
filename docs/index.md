# Getting started

This library is high-level MongoDB driver, which created to replace 
[Legacy MongoDB PHP Driver](https://github.com/mongodb/mongo-php-driver-legacy),
i.e. `pecl/mongo` PHP extension, which for now is deprecated and does not work with PHP 7.
The lib is based on the new official low-level [MongoDB PHP Driver](https://github.com/mongodb/mongo-php-driver),
follows [MongoDB Driver Specifications](https://github.com/mongodb/specifications) 
and defines abstractions like `Client`, `Database`, `Collection` etc. You should use
this classes instead of `\MongoClient`, `\MongoCollection` and other classes from deprecated `mongo` extension.

## Low-level and high-level driver

MongoDB developers decided not to implement all functionality of the legacy driver
in new PHP extension, written in `C` language. Instead, they developed a thin abstraction layer
that allows to execute database commands, read and write queries - 
[`pecl/mongodb`](https://github.com/mongodb/mongo-php-driver) PHP extension. 
The high-level API need to be implemented in PHP lib, such as this.

You can use **low-level** driver without high-level driver like this:

```php
<?php

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Query;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;

$manager = new Manager('mongodb://127.0.0.1/');

// Writes
$bulk = new BulkWrite();
$bulk->insert(['foo' => 'bar']);
$bulk->insert(['bar' => 'baz']);
$bulk->update(['foo' => 'bar'], ['$set' => ['bla' => 'bla-bla']]);

$manager->executeBulkWrite('my_db.my_collection', $bulk);

// Read documents
$cursor = $manager->executeQuery('my_db.my_collection', new Query(['foo' => 'bar']));
echo $cursor->toArray()[0]->bla; // outputs "bla-bla"

// Execute command
$command = new Command(['listIndexes' => 'my_collection']);
$cursor = $manager->executeCommand('my_db', $command, new ReadPreference(ReadPreference::RP_PRIMARY));

foreach ($cursor as $indexInfo) {
    echo $indexInfo['name']; // outputs each index name in collection "my_collection" in db "my_db"
}

// ... or, execute query directly on primary server. This often can be needed to check
// current primary server version to know whether it supports some command or query options
$server = $manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));
$commandOptions = ['drop' => 'my_collection'];
$serverInfo = $server->getInfo();
$minWireVersion = $serverInfo['minWireVersion'];
$maxWireVersion = $serverInfo['maxWireVersion'];
$wireVersionForWriteConcern = 5;
if ($wireVersionForWriteConcern > $minWireVersion && $wireVersionForWriteConcern < $maxWireVersion) {
    // command will return after write operation have been propagated to majority of voting replica set nodes
    $commandOptions['writeConcern'] = new WriteConcern(WriteConcern::MAJORITY);
}

$command = new Command($commandOptions);

$cursor = $server->executeCommand('my_db', $command);
```

Here is how to use this **high-level** driver:
```php
<?php

use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Manager;
use Tequila\MongoDB\Client;
use Tequila\MongoDB\Write\Model\InsertOne;
use Tequila\MongoDB\Write\Model\UpdateOne;

$manager = new Manager('mongodb://127.0.0.1/');
$client = new Client($manager);

// Writes
$collection = $client->selectCollection('my_db', 'my_collection');
$collection->insertOne(['foo' => 'bar']);
$collection->insertOne(['bar' => 'baz']);
$collection->updateOne(['foo' => 'bar'], ['$set' => ['bla' => 'bla-bla']]);

// .. or, to send all write queries in one bulk:
$collection->bulkWrite([
    new InsertOne(['foo' => 'bar']),
    new InsertOne(['bar' => 'baz']),
    new UpdateOne(['foo' => 'bar'], ['$set' => ['bla' => 'bla-bla']]),
]);


// Read documents
$document = $collection->findOne(['foo' => 'bar']);
echo $document->bla; // outputs "bla-bla"

// Execute command 
$indexes = $collection->listIndexes();
foreach ($indexes as $indexInfo) {
    echo $indexInfo['name']; // outputs each index name in collection "my_collection" in db "my_db"
}

// Execute command with "writeConcern" option
// command will return after write operation have been propagated to majority of voting replica set nodes.
// If 'writeConcern' option is not supported by the server, which executes this operation, option will
// be removed before command execution, and command will return after operation have been
// propagated to the primary server.
// For other unsupported options, \Tequila\MongoDB\Exception\UnsupportedException can be thrown.
// This behavior is described in MongoDB Driver Specifications.
$collection->drop(['writeConcern' => new WriteConcern(WriteConcern::MAJORITY)]);
```

## Installation

Since this library is based on a new low-level driver, it requires this driver to be installed.
Here is how to install low-level driver in Debian/Ubuntu:
```bash
sudo pecl install mongodb
```

The library itself should be installed by Composer:

```bash
composer require tequila/mongodb-php-lib
```
