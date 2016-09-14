<?php

namespace Tequilla\MongoDB\Exception;

use MongoDB\Driver\Exception\Exception as MongoDBException;

class LogicException extends \LogicException implements MongoDBException
{
}