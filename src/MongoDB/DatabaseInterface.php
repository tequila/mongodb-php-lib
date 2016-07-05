<?php

namespace Tequilla\MongoDB;

interface DatabaseInterface
{
    public function createCollection(array $options = []);
    
    public function getName();
    
    public function selectCollection($collectionName, array $options = []);

    public function drop(array $options = []);

    public function listCollections(array $options = []);
}