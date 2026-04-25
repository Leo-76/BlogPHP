<?php
namespace App\Console\Commands;

class MakeMiddleware
{
    public function handle(string $name): void
    {
        $path = base_path("src/Middleware/{$name}.php");
        if (file_exists($path)) { echo "  [EXISTE] {$name}.php\n"; return; }

        $stub = <<<PHP
        <?php
        namespace App\Middleware;

        use App\Http\Request;
        use App\Http\Response;

        class {$name}
        {
            public function handle(Request \$request): ?Response
            {
                // Votre logique ici
                // return Response::redirect('/login'); // bloquer
                return null; // continuer
            }
        }
        PHP;

        file_put_contents($path, $stub);
        echo "  [OK] src/Middleware/{$name}.php créé\n";
    }
}
