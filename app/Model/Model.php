<?php

namespace App\Model;

use Exception;
use PDO;
use PDOStatement;
use ReflectionClass;
use ReflectionProperty;
use Core\Connection\DB;

abstract class Model
{
    private static ModelMeta $meta;

    protected static PDO $connection;

    private static PDOStatement $readQuery;

    private static PDOStatement $saveQuery;

    private static PDOStatement $deleteQuery;

    private static PDOStatement $updateQuery;

    /**
     * @throws Exception
     */
    public static function boot(DB $connection): void
    {
        static::$meta = static::getMeta();
        static::$connection = $connection;

        static::setupReadQuery();
        static::setupSaveQuery();
        static::setupDeleteQuery();
        static::setupUpdateQuery();
    }

    public function read(): bool
    {
        self::$readQuery->execute([
            static::$meta->primaryKey => $this->{static::$meta->primaryKey}
        ]);

        $data = self::$readQuery->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            foreach (static::getFieldNames() as $key) {
                $this->{$key} = $data[$key];
            }
        }

        return !!$data;
    }

    public function save(): bool
    {
        $fields = $this->getBindFields([static::$meta->primaryKey]);

        $isSuccess = self::$saveQuery->execute($fields);

        if ($isSuccess) {
            self::$readQuery->execute([
                static::$meta->primaryKey => static::$connection->lastInsertId()
            ]);

            $data = self::$readQuery->fetch(PDO::FETCH_ASSOC);

            $keys = array_keys($fields);
            $keys[] = static::$meta->primaryKey;
            foreach ($keys as $key) {
                $this->{$key} = $data[$key];
            }
        }

        return $isSuccess;
    }

    public function update(): bool
    {
        $fields = $this->getBindFields();

        return self::$updateQuery->execute($fields);
    }

    public function delete(): bool
    {
        return self::$deleteQuery->execute([
            static::$meta->primaryKey => $this->{static::$meta->primaryKey}
        ]);
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

    private static function getFieldNames(array $except = []): array
    {
        $fieldsNames = [];
        foreach (static::getFields() as $field) {
            if (!in_array($field->name, $except)) {
                $fieldsNames[] = $field->name;
            }
        }

        return $fieldsNames;
    }

    private static function setupReadQuery(): void
    {
        $table = static::$meta->table;
        $primaryKey = static::$meta->primaryKey;

        self::$readQuery = self::$connection->prepare("SELECT * FROM $table WHERE $primaryKey = :$primaryKey");
    }

    private static function setupSaveQuery(): void
    {
        $fieldsNames = static::getFieldNames([static::$meta->primaryKey]);

        $table = static::$meta->table;
        $fields = implode(', ', $fieldsNames);
        $values = implode(', ', array_map(fn ($it) => ":$it", $fieldsNames));

        self::$saveQuery = self::$connection->prepare("INSERT INTO $table ($fields) VALUES ($values)");
    }

    private static function setupDeleteQuery(): void
    {
        $table = static::$meta->table;
        $primaryKey = static::$meta->primaryKey;

        self::$deleteQuery = self::$connection->prepare("DELETE FROM $table WHERE $primaryKey = :$primaryKey");
    }

    private static function setupUpdateQuery(): void
    {
        $table = static::$meta->table;
        $primaryKey = static::$meta->primaryKey;

        $fieldsToSet = [];
        foreach (static::getFields() as $field) {
            if ($field->name !== static::$meta->primaryKey) {
                $fieldsToSet[] = "$field->name = :$field->name";
            }
        }
        $fieldsToSet = implode(', ', $fieldsToSet);

        self::$updateQuery = self::$connection->prepare("UPDATE $table SET $fieldsToSet WHERE $primaryKey = :$primaryKey");
    }

    /**
     * @throws Exception
     */
    public static function getMeta(): ModelMeta
    {
        $meta = (new ReflectionClass(static::class))->getAttributes(ModelMeta::class);
        if (!count($meta)) {
            /** @var class-string<Model> $parent */
            $parent = get_parent_class(static::class);

            if ($parent) {
                return $parent::getMeta();
            }
            throw new Exception('Meta must be defined in class: ' . static::class);
        }

        return $meta[0]->newInstance();
    }

    /**
     * @return array<ReflectionProperty>
     */
    public static function getFields(): array
    {
        return array_filter(
            (new ReflectionClass(static::class))->getProperties(),
            fn ($it) => $it->isPublic() && !$it->isReadOnly() && !$it->isStatic()
        );
    }
}