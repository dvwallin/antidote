<?php

class Antidote_migration
{
    protected $object_dbh = NULL;
    function __construct ( $object_dbh ) 
    {
        $this->object_dbh = $object_dbh;
    }
}

