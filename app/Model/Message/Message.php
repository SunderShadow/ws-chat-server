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

    private static PDOStatement $readQuery;

    private static PDOStatement $saveQuery;

    /** @var callable */
    private static $getSavedRecord;

    // TODO: reduce code (see \App\Model\User\User.php)
    public static function boot(DB $connection)
    {
        self::$readQuery = $connection->prepare('SELECT * FROM messages WHERE id = :id');
        self::$saveQuery = $connection->prepare('INSERT INTO messages(text, sender_id) VALUES (:text, :sender_id)');
        self::$getSavedRecord = static function () use ($connection) {
            self::$readQuery->execute([
                'id' => $connection->lastInsertId()
            ]);
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
            $this->text = $data['text'];
            $this->sender_id = $data['sender_id'];
        }

        return !!$data;
    }

    public function save(): bool
    {
        $isSuccess = self::$saveQuery->execute([
            'text' => $this->text,
            'sender_id' => $this->sender_id
        ]);

        if ($isSuccess) {
            $data = (self::$getSavedRecord)();

            $this->id = $data['id'];
            $this->text = $data['text'];
            $this->sender_id = $data['sender_id'];
        }

        return $isSuccess;
    }
}