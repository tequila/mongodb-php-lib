<?php

namespace Tequilla\MongoDB\Exception;

use MongoDB\Driver\Exception\Exception as MongoDBException;

class InvalidArgumentException extends \MongoDB\Driver\Exception\InvalidArgumentException implements MongoDBException
{
}
