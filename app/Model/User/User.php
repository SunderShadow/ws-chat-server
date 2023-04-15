<?php

namespace App\Model\User;

use App\Model\Message\Message;
use App\Model\Model;

class User extends Model
{
    public int $id;

    public string $name;

    protected static string $table = 'users';

    protected static string $primaryKey = 'id';

    public function attachMessage(Message $message): bool
    {
        $message->sender_id = $this->id;
        return $message->save();
    }
}