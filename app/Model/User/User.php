<?php

namespace App\Model\User;

use App\Model\Message\Message;
use App\Model\Model;
use App\Model\ModelMeta;

#[ModelMeta(
    primaryKey: 'id',
    table: 'users'
)]
class User extends Model
{
    public int $id;

    public string $name;

    public function attachMessage(Message $message): bool
    {
        $message->sender_id = $this->id;
        return $message->create();
    }
}