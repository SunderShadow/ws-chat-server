<?php

namespace App\Model;

use PDO;
use PDOStatement;

readonly class ModelQueries
{
    public PDOStatement $readQuery;

    public PDOStatement $createQuery;

    public PDOStatement $deleteQuery;

    public PDOStatement $updateQuery;

    public function __construct(
        private ModelMeta $meta,
        private PDO $connection,
        private array $fields
    )
    {
        $this->setupReadQuery();
        $this->setupSaveQuery();
        $this->setupDeleteQuery();
        $this->setupUpdateQuery();
    }

    private function setupReadQuery(): void
    {
        $table = $this->meta->table;
        $primaryKey = $this->meta->primaryKey;

        $this->readQuery = $this->connection->prepare("SELECT * FROM $table WHERE $primaryKey = :$primaryKey");
    }

    private function setupSaveQuery(): void
    {
        $fieldsNames = [];
        foreach ($this->fields as $field) {
            if ($field->name !== 'id') {
                $fieldsNames[] = $field->name;
            }
        }

        $table = $this->meta->table;
        $fields = implode(', ', $fieldsNames);
        $values = implode(', ', array_map(fn ($it) => ":$it", $fieldsNames));

        $this->createQuery = $this->connection->prepare("INSERT INTO $table ($fields) VALUES ($values)");
    }

    private function setupDeleteQuery(): void
    {
        $table = $this->meta->table;
        $primaryKey = $this->meta->primaryKey;

        $this->deleteQuery = $this->connection->prepare("DELETE FROM $table WHERE $primaryKey = :$primaryKey");
    }

    private function setupUpdateQuery(): void
    {
        $table = $this->meta->table;
        $primaryKey = $this->meta->primaryKey;

        $fieldsToSet = [];
        foreach ($this->fields as $field) {
            if ($field->name !== $this->meta->primaryKey) {
                $fieldsToSet[] = "$field->name = :$field->name";
            }
        }
        $fieldsToSet = implode(', ', $fieldsToSet);

        $this->updateQuery = $this->connection->prepare("UPDATE $table SET $fieldsToSet WHERE $primaryKey = :$primaryKey");
    }
}