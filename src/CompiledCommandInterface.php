<?php

namespace Tequila\MongoDB;

interface CompiledCommandInterface
{
    /**
     * @param ManagerInterface $manager
     * @param $databaseName
     * @return CursorInterface
     */
    public function execute(ManagerInterface $manager, $databaseName);
}