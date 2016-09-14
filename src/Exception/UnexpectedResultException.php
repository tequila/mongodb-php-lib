<?php

namespace Tequilla\MongoDB\Exception;

use MongoDB\Driver\Exception\Exception as MongoDBException;

class UnexpectedResultException extends \UnexpectedValueException implements MongoDBException
{
}