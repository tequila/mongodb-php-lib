<?php

namespace Tequila\MongoDB\Command\Traits;

use MongoDB\Driver\WriteConcern;

trait ConvertWriteConcernToDocumentTrait
{
    private static function convertWriteConcernToDocument(WriteConcern $writeConcern)
    {
        $writeConcernOptions = [];

        if (null !== ($w = $writeConcern->getW())) {
            $writeConcernOptions['w'] = $w;
        }

        if (null !== ($j = $writeConcern->getJournal())) {
            $writeConcernOptions['j'] = $j;
        }

        if (null !== ($wTimeout = $writeConcern->getWtimeout())) {
            $writeConcernOptions['wtimeout'] = $wTimeout;
        }

        return (object)$writeConcernOptions;
    }
}