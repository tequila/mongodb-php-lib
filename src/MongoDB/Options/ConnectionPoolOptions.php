<?php

namespace Tequilla\MongoDB\Options;

/**
 * Class ConnectionPoolOptions
 * @package Tequilla\MongoDB\Options
 */
final class ConnectionPoolOptions implements OptionsInterface
{
    const MAX_POOL_SIZE = 'maxPoolSize';
    const MIN_POOL_SIZE = 'minPoolSize';
    const MAX_IDLE_TIME_MS = 'maxIdleTimeMS';
    const WAIT_QUEUE_MULTIPLE = 'waitQueueMultiple';
    const WAIT_QUEUE_TIMEOUT_MS = 'waitQueueTimeoutMS';
    
    public static function getAll()
    {
        return [
            self::MAX_POOL_SIZE,
            self::MIN_POOL_SIZE,
            self::MAX_IDLE_TIME_MS,
            self::WAIT_QUEUE_MULTIPLE,
            self::WAIT_QUEUE_TIMEOUT_MS,
        ];
    }
}