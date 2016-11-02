<?php

namespace Tequila\MongoDB\Tests\Traits;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;

trait InvalidValuesTrait
{
    private function getInvalidArrayValues()
    {
        return [1, 'foo', false, null, 3.14, new \stdClass()];
    }

    private function getInvalidBoolValues()
    {
        return [1, 0, 'foo', null, 3.14, new \stdClass(), []];
    }

    private function getInvalidDocumentValues()
    {
        return [1, 0, 'foo', false, null, 3.14];
    }

    private function getInvalidIntegerValues()
    {
        return ['foo', false, null, 3.14, new \stdClass(), []];
    }

    private function getInvalidReadConcernValues()
    {
        return [
            1,
            'foo',
            false,
            null,
            3.14,
            new \stdClass(),
            [],
            new ReadPreference(ReadPreference::RP_PRIMARY),
            new WriteConcern(WriteConcern::MAJORITY),
        ];
    }

    private function getInvalidReadPreferenceValues()
    {
        return [
            1,
            'foo',
            false,
            null,
            3.14,
            new \stdClass(),
            [],
            new ReadConcern(ReadConcern::LOCAL),
            new WriteConcern(WriteConcern::MAJORITY),
        ];
    }

    private function getInvalidWriteConcernValues()
    {
        return [
            1,
            'foo',
            false,
            null,
            3.14,
            new \stdClass(),
            [],
            new ReadConcern(ReadConcern::LOCAL),
            new ReadPreference(ReadPreference::RP_NEAREST),
        ];
    }

    private function getInvalidTypeMapValues()
    {
        return [
            ['array' => 'foo'],
            ['root' => 'bar'],
            ['document' => 'baz'],
            ['root' => 'My\ClassThatDoesNotExists']
        ];
    }
}