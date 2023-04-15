<?php

namespace App\Model;

use Core\Connection\DB;
use PDO;
use PDOStatement;
use ReflectionProperty;

abstract class Model
{
    protected static string $primaryKey;

    protected static string $table;

    protected static PDO $connection;

    private static PDOStatement $readQuery;

    private static PDOStatement $saveQuery;

    public static function boot(DB $connection): void
    {
        static::$connection = $connection;
        static::setupReadQuery();
        static::setupSaveQuery();
    }

    public function read(): bool
    {
        self::$readQuery->execute([
            static::$primaryKey => $this->{static::$primaryKey}
        ]);

        $data = self::$readQuery->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            foreach (array_keys($this->getBindFields()) as $key) {
                $this->{$key} = $data[$key];
            }
        }

        return !!$data;
    }

    public function save(): bool
    {
        $fields = $this->getBindFields(['id']);

        $isSuccess = self::$saveQuery->execute($fields);

        if ($isSuccess) {
            self::$readQuery->execute([
                static::$primaryKey => static::$connection->lastInsertId()
            ]);

            $data = self::$readQuery->fetch(PDO::FETCH_ASSOC);

            $keys = array_keys($fields);
            $keys[] = static::$primaryKey;
            foreach ($keys as $key) {
                $this->{$key} = $data[$key];
            }
        }

        return $isSuccess;
    }

    private function getBindFields(array $except = []): array
    {
        $fields = [];
        foreach (static::getFields() as $field) {
            if (!in_array($field->name, $except)) {
                $fields[$field->name] = $this->{$field->name};
            }
        }

        return $fields;
    }

    private static function setupReadQuery(): void
    {
        $table = static::$table;
        $primaryKey = static::$primaryKey;

        self::$readQuery = self::$connection->prepare("SELECT * FROM $table WHERE $primaryKey = :$primaryKey");
    }

    private static function setupSaveQuery(): void
    {
        $fieldsNames = [];
        foreach (static::getFields() as $field) {
            if ($field->name !== static::$primaryKey) {
                $fieldsNames[] = $field->name;
            }
        }

        $table = static::$table;
        $fields = implode(', ', $fieldsNames);
        $values = implode(', ', array_map(fn ($it) => ":$it", $fieldsNames));

        self::$saveQuery = self::$connection->prepare("INSERT INTO $table ($fields) VALUES ($values)");
    }

    /**
     * @return array<ReflectionProperty>
     */
    public static function getFields(): array
    {
        return array_filter(
            (new \ReflectionClass(static::class))->getProperties(),
            fn ($it) => $it->isPublic() && !$it->isReadOnly() && !$it->isStatic()
        );
    }
}