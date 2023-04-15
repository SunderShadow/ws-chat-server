<?php

namespace App\Model;

use Exception;
use PDO;
use ReflectionClass;
use ReflectionProperty;
use Core\Connection\DB;

abstract class Model
{
    protected static ModelMeta $meta;

    protected static PDO $connection;

    protected static ModelQueries $queries;

    /**
     * @throws Exception
     */
    public static function boot(DB $connection): void
    {
        static::$meta = static::getMeta();
        static::$connection = $connection;
        static::$queries = new ModelQueries(
            static::$meta,
            static::$connection,
            static::getFields()
        );
    }

    public function create(): bool
    {
        $fields = [];
        foreach (static::getFields() as $field) {
            if ($field->name !== static::$meta->primaryKey){
                $fields[$field->name] = $this->{$field->name};
            }
        }

        if ($isSuccess = self::$queries->createQuery->execute($fields)) {
            self::$queries->readQuery->execute([
                static::$meta->primaryKey => static::$connection->lastInsertId()
            ]);

            $data = self::$queries->readQuery->fetch(PDO::FETCH_ASSOC);

            foreach (static::getFields() as $field) {
                $this->{$field->name} = $data[$field->name];
            }
        }

        return $isSuccess;
    }
    public function read(): bool
    {
        self::$queries->readQuery->execute([
            static::$meta->primaryKey => $this->{static::$meta->primaryKey}
        ]);

        if ($data = self::$queries->readQuery->fetch(PDO::FETCH_ASSOC)) {
            foreach (static::getFields() as $field) {
                $this->{$field->name} = $data[$field->name];
            }
        }

        return !!$data;
    }

    public function update(): bool
    {
        $fields = [];
        foreach (static::getFields() as $field) {
            $fields[$field->name] = $this->{$field->name};
        }

        return self::$queries->updateQuery->execute($fields);
    }

    public function delete(): bool
    {
        return self::$queries->deleteQuery->execute([
            static::$meta->primaryKey => $this->{static::$meta->primaryKey}
        ]);
    }

    /**
     * @throws Exception
     */
    private static function getMeta(): ModelMeta
    {
        $meta = (new ReflectionClass(static::class))->getAttributes(ModelMeta::class);
        if (!count($meta)) {
            /** @var class-string<Model> $parent */
            if ($parent = get_parent_class(static::class)) {
                return $parent::getMeta();
            }

            throw new Exception('Meta must be defined in ' . static::class);
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
            fn($it) => $it->isPublic() && !$it->isReadOnly() && !$it->isStatic()
        );
    }
}