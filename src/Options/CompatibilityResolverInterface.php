<?php

namespace Tequila\MongoDB\Options;

interface CompatibilityResolverInterface
{
    /**
     * @param ServerCompatibleOptions $options
     * @void
     */
    public function resolveCompatibilities(ServerCompatibleOptions $options);
}