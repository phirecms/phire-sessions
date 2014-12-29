<?php

namespace Sessions\Table;

use Pop\Db\Record;

class UserSessionConfigs extends Record
{

    /**
     * Table prefix
     * @var string
     */
    protected static $prefix = DB_PREFIX;

    /**
     * Primary keys
     * @var array
     */
    protected $primaryKeys = ['role_id'];

}