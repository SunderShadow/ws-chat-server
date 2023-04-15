<?php

namespace App\Model\Message;

use App\Model\Model;
use Core\Connection\DB;
use PDOStatement;

class Message extends Model
{
    public int $id;

    public string $text;

    public int $sender_id;

    protected static string $table = 'messages';

    protected static string $primaryKey = 'id';
}