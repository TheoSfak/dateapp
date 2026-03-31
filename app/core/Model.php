<?php
namespace App\Core;

/**
 * Base Model – thin wrapper providing DB access to child models.
 */
abstract class Model
{
    protected static string $table = '';

    protected static function db(): Database
    {
        return Database::getInstance();
    }

    public static function findById(int $id): ?array
    {
        $table = static::$table;
        $stmt  = static::db()->query("SELECT * FROM {$table} WHERE id = ? LIMIT 1", [$id]);
        $row   = $stmt->fetch();
        return $row ?: null;
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        $table = static::$table;
        $stmt  = static::db()->query("SELECT * FROM {$table} WHERE {$column} = ? LIMIT 1", [$value]);
        $row   = $stmt->fetch();
        return $row ?: null;
    }
}
