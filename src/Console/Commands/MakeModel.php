<?php
namespace App\Console\Commands;

class MakeModel
{
    public function handle(string $name, bool $withMigration = false): void
    {
        $path = base_path("src/Models/{$name}.php");
        if (file_exists($path)) { echo "  [EXISTE] {$name}.php\n"; return; }

        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name)) . 's';
        $stub  = <<<PHP
        <?php
        namespace App\Models;

        use App\Database\Model;
        use App\Database\DB;

        class {$name} extends Model
        {
            protected static string \$table = '{$table}';

            // Ajouter vos méthodes personnalisées ici
            // public static function active(): array
            // {
            //     return DB::query('SELECT * FROM {$table} WHERE actif = 1');
            // }
        }
        PHP;

        file_put_contents($path, $stub);
        echo "  [OK] src/Models/{$name}.php créé\n";

        if ($withMigration) {
            (new MakeMigration())->handle("create_{$table}_table", $table);
        }
    }
}
