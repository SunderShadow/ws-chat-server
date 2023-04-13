<?php

namespace App\Model;

use Core\Connection\DB;

abstract class Model
{
    abstract public static function boot(DB $connection);
}