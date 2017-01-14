---
title: Tequila MongoDB PHP Library
---

# Getting started

This high-level MongoDB driver was created to replace 
[Legacy MongoDB PHP Driver](https://github.com/mongodb/mongo-php-driver-legacy),
i.e. `pecl/mongo` PHP extension, which for now is deprecated and does not work with PHP 7.
This library is based on the new official low-level [MongoDB PHP Driver](https://github.com/mongodb/mongo-php-driver),
follows [MongoDB Driver Specifications](https://github.com/mongodb/specifications) 
and defines abstractions like `Client`, `Database`, `Collection` etc.

## Installation

Since this library is based on a new low-level driver, it requires this driver to be installed:

```bash
sudo pecl install mongodb
```

The library itself should be installed by Composer:

```bash
composer require tequila/mongodb-php-lib
```

## Usage
All new drivers for MongoDB should follow [MongoDB Driver Specifications](https://github.com/mongodb/specifications).
By following this specifications, this library introduces different changes beside the API
of the legacy driver.
Below there are examples of how to do common tasks using this driver in comparison with te API of the legacy driver:

### CRUD

#### Insert one document

Using this driver:

```php
<?php

$manager = new \Tequila\MongoDB\Manager('mongodb://127.0.0.1/');
$client = new \Tequila\MongoDB\Client($manager);
$collection = $client->selectCollection('test', 'test');
$collection->insertOne(['foo' => 'bar']);
```

Using the legacy driver:

```php
<?php

$client = new \MongoClient('mongodb://127.0.0.1/');
$collection = $client->selectCollection('test', 'test');
$collection->insert(['foo' => 'bar']);
```

#### Insert many documents
Using this driver:

```php
<?php

/** @var \Tequila\MongoDB\Collection $collection */
$collection->insertMany([
    ['foo' => 'bar'],
    ['bar' => 'baz'],
    ['baz' => 'foo'],
]);
```

Using the legacy driver:

```php
<?php

/** @var \MongoCollection $collection */
$batch = new \MongoInsertBatch($collection);
$batch->add(['foo' => 'bar']);
$batch->add(['bar' => 'baz']);
$batch->add(['baz' => 'foo']);

$batch->execute();
```

#### Update one document

Using this driver:

```php
<?php

use MongoDB\BSON\UTCDateTime;

/** @var \Tequila\MongoDB\Collection $collection */
$collection->updateOne(['foo' => 'bar'], ['$set' => ['updated_at' => new UTCDateTime()]]);
```

Using the legacy driver:

```php
<?php

/** @var \MongoCollection $collection */
$collection->update(
    ['foo' => 'bar'],
    ['$set' => ['updated_at' => new \MongoDate()]],
    ['multiple' => false]
);
```

#### Replace one document

Using this driver:

```php
<?php

// Collection::replaceOne() will trow an exception if a second argument contains
// update operators like $inc, $set etc.
// Collection::updateOne() will throw an exception if a second argument is a replacement document.

/** @var \Tequila\MongoDB\Collection $collection */
$collection->replaceOne(['foo' => 'bar'], ['bar' => 'baz']);
```

Using the legacy driver:

```php
<?php

/** @var \MongoCollection $collection */
$collection->update(
    ['foo' => 'bar'],
    ['bar' => 'baz'],
    ['multiple' => false]
);
```

#### Update many documents

Using this driver:

```php
<?php

/** @var \Tequila\MongoDB\Collection $collection */
$collection->updateMany(
    ['foo' => ['$exists' => true]], 
    ['$set' => ['isTestDocument' => true]]
);
```

Using the legacy driver:

```php
<?php

/** @var \MongoCollection $collection */
$collection->update(
    ['foo' => ['$exists' => true]], 
    ['$set' => ['isTestDocument' => true]],
    ['multiple' => true]
);
```

#### Delete one document

Using this driver:

```php
<?php

/** @var \Tequila\MongoDB\Collection $collection */
$collection->deleteOne(['foo' => 'bar']);
```

Using the legacy driver:

```php
<?php

/** @var \MongoCollection $collection */
$collection->remove(['foo' => 'bar'], ['justOne' => true]);
```

#### Delete many documents

Using this driver:

```php
<?php

/** @var \Tequila\MongoDB\Collection $collection */
$collection->deleteMany(['foo' => 'bar']);
```

Using the legacy driver:

```php
<?php

/** @var \MongoCollection $collection */
$collection->remove(['foo' => 'bar'], ['justOne' => false]);
```

#### Bulk writes

Legacy driver supports bulk writes of the same type, e.g. bulk inserts, bulk updates or bulk deletes.
For this tasks legacy driver has three classes - `\MongoInsertBatch`, `\MongoUpdateBatch`
and `\MongoDeleteBatch`. To add operation to a batch, you must use a weird syntax:

```php
<?php

/** @var \MongoCollection $collection */
$batch = new \MongoUpdateBatch($collection);

$batch->add([
    'q' => ['foo' => 'bar'], // query (filter) document
    'u' => ['set' => ['updated_at' => new \MongoDate()]] // update document
]);

$batch->execute();

```

The new low-level MongoDB driver allows to mix different types of writes in one bulk.
This means that you could add different write operations to one bulk, and even chain
this operations, for example insert a document and than update many documents, including
inserted one.
The MongoDB Driver Specifications has a definition of "write models". 
Write model - is an object that specifies the type of write operation and it's arguments.
Tequila MongoDB PHP Lib implements all write models from driver specifications. 
There are 6 write model classes: `InsertOne`, `UpdateOne`, `UpdateMany`, `ReplaceOne`,
`DeleteOne` and `DeleteMany`. All this classes reside in namespace `Tequila\MongoDB\Write\Model`.

Here is an example of bulk writes functionality usage in this driver:

```php
<?php

use Tequila\MongoDB\Write\Model\InsertOne;
use Tequila\MongoDB\Write\Model\UpdateMany;
use Tequila\MongoDB\Write\Model\UpdateOne;
use Tequila\MongoDB\Write\Model\DeleteMany;

/** @var \Tequila\MongoDB\Collection $collection */
$collection->bulkWrite([
    new InsertOne(['firstName' => 'Trevor', 'lastName' => 'Philips']),
    new InsertOne(['firstName' => 'Michael', 'lastName' => 'De Santa']),
    new InsertOne(['firstName' => 'Franklin', 'lastName' => 'Clinton']),
    new InsertOne(['firstName' => 'Bradley', 'lastName' => 'Snider']),
    new UpdateMany([], ['$set' => ['sex' => 'male']]),
    new UpdateOne(['firstName' => 'Bradley'], ['$set' => ['alive' => false]]),
    new DeleteMany(['alive' => false]),
]);

$cursor = $collection->find();

echo $cursor->current()['firstName']; // outputs "Trevor"
echo $cursor->current()['sex']; // outputs "male"

$collection->findOne(['firstName' => 'Bradley']); // returns null
```

#### Write results

The only way to insert, delete or update documents in collection is to use
the bulk write API, provided by the low-level driver.
At high-level, bulk writes are done using `Tequila\MongoDB\Collection::bulkWrite()`
method and write models (see example above).
This method returns an instance of `Tequila\MongoDB\WriteResult` class.
This class wraps the `MongoDB\Driver\WriteResult` class and adds functionality of inserted ids.
Other write methods, such as `Collection::insertOne()` or `Collection::updateMany()`
internally use `Collection::bulkWrite()` method and wrap `Tequila\MongoDB\WriteResult`
to provide their results, such as `Tequila\MongoDB\Write\Result\InsertOneResult`
or `Tequila\MongoDB\Write\Result\UpdateResult`.

#### Id of inserted document

The legacy driver takes care of setting the id of inserted document to this document.
New driver does not do this, and it's up to user to decide, what to do with id of an inserted document:

```php
<?php

/** @var \Tequila\MongoDB\Collection $collection */
$document = new \stdClass();
$document->firstName = 'Trevor';
$document->lastName = 'Philips';

/** @var \Tequila\MongoDB\Write\Result\InsertOneResult $result */
$result = $collection->insertOne($document);
$document->_id = $result->getInsertedId();

// or, for a bulk inserts:
$documents = [
    ['firstName' => 'Trevor', 'lastName' => 'Philips'],
    ['firstName' => 'Michael', 'lastName' => 'De Santa'],
    ['firstName' => 'Franklin', 'lastName' => 'Clinton'],
];

/** @var \Tequila\MongoDB\Write\Result\InsertManyResult $result */
$result = $collection->insertMany($documents);

// InsertManyResult::getInsertedIds() and WriteResult::getInsertedIds() returns
// an array of inserted ids, where the key is the position of the inserted document 
// and value is an id.
foreach ($result->getInsertedIds() as $position => $id) {
    $documents[$position]['_id'] = $id;
}
```
