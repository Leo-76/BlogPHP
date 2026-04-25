<?php
namespace App\Database;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public static function table(): string
    {
        if (static::$table) return static::$table;
        $class = (new \ReflectionClass(static::class))->getShortName();
        return strtolower($class) . 's';
    }

    public static function all(): array
    {
        return DB::query('SELECT * FROM ' . static::table());
    }

    public static function find(int $id): array|false
    {
        return DB::find(
            'SELECT * FROM ' . static::table() . ' WHERE ' . static::$primaryKey . ' = ?',
            [$id]
        );
    }

    public static function findBy(string $column, mixed $value): array|false
    {
        return DB::find('SELECT * FROM ' . static::table() . " WHERE {$column} = ?", [$value]);
    }

    public static function where(string $column, mixed $value): array
    {
        return DB::query('SELECT * FROM ' . static::table() . " WHERE {$column} = ?", [$value]);
    }

    public static function create(array $data): int|string
    {
        $cols = implode(', ', array_keys($data));
        $vals = implode(', ', array_fill(0, count($data), '?'));
        DB::execute("INSERT INTO " . static::table() . " ({$cols}) VALUES ({$vals})", array_values($data));
        return DB::lastId();
    }

    public static function update(int $id, array $data): bool
    {
        $sets = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
        return DB::execute(
            "UPDATE " . static::table() . " SET {$sets}, updated_at = datetime('now') WHERE " . static::$primaryKey . " = ?",
            [...array_values($data), $id]
        );
    }

    public static function delete(int $id): bool
    {
        return DB::execute("DELETE FROM " . static::table() . " WHERE " . static::$primaryKey . " = ?", [$id]);
    }

    public static function count(): int
    {
        return (int) DB::find('SELECT COUNT(*) as n FROM ' . static::table())['n'];
    }

    public static function paginate(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $items  = DB::query('SELECT * FROM ' . static::table() . " ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");
        $total  = static::count();
        return [
            'items'       => $items,
            'total'       => $total,
            'per_page'    => $perPage,
            'current'     => $page,
            'last_page'   => (int) ceil($total / $perPage),
        ];
    }
}
