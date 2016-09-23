<?php

namespace Tequila\MongoDB\Exception;

use MongoDB\Driver\Exception\UnexpectedValueException;

class UnexpectedResultException extends UnexpectedValueException implements Exception
{
}