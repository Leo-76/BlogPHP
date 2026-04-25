<?php
namespace App\Console\Commands;

class MakeMigration
{
    public function handle(string $name, string $table = ''): void
    {
        $timestamp = date('Y_m_d_His');
        $filename  = "{$timestamp}_{$name}.php";
        $path      = base_path("database/migrations/{$filename}");

        $table = $table ?: str_replace(['create_', '_table'], '', $name);
        $stub  = <<<PHP
        <?php
        return new class {
            public function up(\PDO \$pdo): void
            {
                \$pdo->exec("CREATE TABLE IF NOT EXISTS {$table} (
                    id         INTEGER PRIMARY KEY AUTOINCREMENT,
                    created_at TEXT DEFAULT (datetime('now')),
                    updated_at TEXT DEFAULT (datetime('now'))
                )");
            }

            public function down(\PDO \$pdo): void
            {
                \$pdo->exec("DROP TABLE IF EXISTS {$table}");
            }
        };
        PHP;

        file_put_contents($path, $stub);
        echo "  [OK] database/migrations/{$filename} créé\n";
    }
}
