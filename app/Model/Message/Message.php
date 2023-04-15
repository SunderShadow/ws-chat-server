<?php

namespace App\Model\Message;

use App\Model\Model;
use App\Model\ModelMeta;
use Core\Connection\DB;
use PDOStatement;

#[ModelMeta(
    primaryKey: 'id',
    table: 'messages'
)]
class Message extends Model
{
    public int $id;

    public string $text;

    public int $sender_id;
}