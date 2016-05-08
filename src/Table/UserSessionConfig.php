<?php

namespace Phire\Sessions\Table;

use Pop\Db\Record;

class UserSessionConfig extends Record
{

    /**
     * Table prefix
     * @var string
     */
    protected $prefix = DB_PREFIX;

    /**
     * Primary keys
     * @var array
     */
    protected $primaryKeys = ['role_id'];

}