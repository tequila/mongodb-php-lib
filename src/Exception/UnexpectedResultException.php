<?php

namespace Tequilla\MongoDB\Exception;

use MongoDB\Driver\Exception\UnexpectedValueException;

class UnexpectedResultException extends UnexpectedValueException implements Exception
{
}