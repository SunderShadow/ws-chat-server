<?php

namespace App\Model\User;

use App\Model\Message\Message;
use App\Model\Model;
use Core\Connection\DB;
use PDOStatement;

class User extends Model
{
    public int $id;

    public string $name;

    private static PDOStatement $readQuery;

    private static PDOStatement $saveQuery;

    /** @var callable */
    private static $getSavedRecord;

    // TODO: reduce code (see \App\Model\Message\Message.php)
    public static function boot(DB $connection): void
    {
        self::$readQuery = $connection->prepare('SELECT * FROM users WHERE id = :id');
        self::$saveQuery = $connection->prepare('INSERT into users(name) VALUE(:name)');
        self::$getSavedRecord = static function () use ($connection) {
            self::$readQuery->execute(compact([
                'id' => $connection->lastInsertId()
            ]));
            return self::$readQuery->fetch(\PDO::FETCH_ASSOC);
        };
    }

    public function read(): bool
    {
        self::$readQuery->execute([
            'id' => $this->id
        ]);

        $data = self::$readQuery->fetch(\PDO::FETCH_ASSOC);
        if ($data) {
            $this->id = $data['id'];
            $this->name = $data['name'];
        }

        return !!$data;
    }

    public function save(): bool
    {
        $isSuccess = self::$saveQuery->execute([
            'name' => $this->name,
        ]);

        if ($isSuccess) {
            $data = (self::$getSavedRecord)();

            $this->id = $data['id'];
        }

        return $isSuccess;
    }

    public function attachMessage(Message $message): bool
    {
        $message->sender_id = $this->id;
        return $message->save();
    }
}