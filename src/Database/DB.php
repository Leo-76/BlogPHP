<?php
namespace App\Database;

use Doctrine\DBAL\Connection;

/**
 * Facade DB basée sur doctrine/dbal
 * API identique à notre DB maison + méthodes DBAL supplémentaires
 */
class DB
{
    public static function connection(): Connection
    {
        return app('db');
    }

    // ── API compatible avec notre DB maison ──────────────────────
    public static function query(string $sql, array $params = []): array
    {
        return static::connection()->fetchAllAssociative($sql, $params);
    }

    public static function find(string $sql, array $params = []): array|false
    {
        $result = static::connection()->fetchAssociative($sql, $params);
        return $result ?: false;
    }

    public static function execute(string $sql, array $params = []): bool
    {
        return static::connection()->executeStatement($sql, $params) !== false;
    }

    public static function lastId(): string
    {
        return static::connection()->lastInsertId();
    }

    // ── API DBAL avancée ─────────────────────────────────────────

    /** Query Builder Doctrine */
    public static function table(string $table): \Doctrine\DBAL\Query\QueryBuilder
    {
        return static::connection()->createQueryBuilder()->from($table);
    }

    public static function transaction(callable $callback): mixed
    {
        return static::connection()->transactional($callback);
    }

    public static function fetchOne(string $sql, array $params = []): mixed
    {
        return static::connection()->fetchOne($sql, $params);
    }

    public static function insert(string $table, array $data): int
    {
        static::connection()->insert($table, $data);
        return (int)static::connection()->lastInsertId();
    }

    public static function update(string $table, array $data, array $where): int
    {
        return static::connection()->update($table, $data, $where);
    }

    public static function delete(string $table, array $where): int
    {
        return static::connection()->delete($table, $where);
    }

    /** Schema Builder pour les migrations */
    public static function schema(): \Doctrine\DBAL\Schema\AbstractSchemaManager
    {
        return static::connection()->createSchemaManager();
    }
}
