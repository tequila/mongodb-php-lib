<?php

namespace Tequilla\MongoDB;

class Events
{
    const BEFORE_DATABASE_SELECTED = 'tequilla.mongodb.before.database.selected';
    const DATABASE_SELECTED = 'tequilla.mongodb.database.selected';
    const BEFORE_DATABASE_DROPPED = 'tequilla.mongodb.before.database.dropped';
    const DATABASE_DROPPED = 'tequilla.mongodb.database.dropped';
    const BEFORE_DATABASE_COMMAND_EXECUTED = 'tequilla.mongodb.before.database.command.executed';
    const DATABASE_COMMAND_EXECUTED = 'tequilla.mongodb.database.command.executed';
    const INDEXES_CREATED = 'tequilla.mongodb.indexes.created';
}
