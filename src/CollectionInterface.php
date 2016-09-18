<?php

namespace Tequilla\MongoDB;

interface CollectionInterface
{
    public function aggregate(array $pipeline, array $options = []);

    public function count(array $filter, array $options = []);

    public function distinct($fieldName, array $filter, array $options = []);

    /**
     * @return array
     */
    public function drop();

    public function find(array $filter, array $options = []);

    /**
     * @param array $requests
     * @param array $options
     * @return \Tequilla\MongoDB\Write\Bulk\BulkWriteResult
     */
    public function bulkWrite(array $requests, array $options = []);

    public function insertOne($document, array $options = []);

    public function insertMany($documents, array $options = []);

    public function deleteOne(array $filter);

    public function deleteMany(array $filter);

    public function replaceOne(array $filter, $replacement, array $options = []);

    public function updateOne(array $filter, array $update, array $options = []);

    public function updateMany(array $filter, array $update, array $options = []);
}