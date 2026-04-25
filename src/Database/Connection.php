<?php
namespace App\Database;

class Connection extends \PDO
{
    public function __construct()
    {
        $driver = env('DB_CONNECTION', 'sqlite');

        $dsn = match($driver) {
            'sqlite' => 'sqlite:' . base_path(env('DB_DATABASE', 'database/database.sqlite')),
            'mysql'  => sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                env('DB_HOST', '127.0.0.1'),
                env('DB_PORT', '3306'),
                env('DB_DATABASE', 'blog')
            ),
            default  => throw new \RuntimeException("Driver [{$driver}] non supporté."),
        };

        parent::__construct($dsn, env('DB_USERNAME'), env('DB_PASSWORD'), [
            self::ATTR_ERRMODE            => self::ERRMODE_EXCEPTION,
            self::ATTR_DEFAULT_FETCH_MODE => self::FETCH_ASSOC,
            self::ATTR_EMULATE_PREPARES   => false,
        ]);

        if ($driver === 'sqlite') {
            $this->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');
        }
    }
}
