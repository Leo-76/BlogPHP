<?php
namespace App\Console\Commands;

class MakeController
{
    public function handle(string $name): void
    {
        $path = base_path("src/Controllers/{$name}.php");
        if (file_exists($path)) { echo "  [EXISTE] {$name}.php\n"; return; }

        $isApi = str_ends_with($name, 'ApiController');
        $extends = $isApi ? "ApiController" : "Controller";
        $use     = $isApi
            ? "use App\\Api\\ApiController;"
            : "use App\\Http\\Controller;";

        $stub = <<<PHP
        <?php
        namespace App\Controllers;

        {$use}
        use App\Http\Request;
        use App\Http\Response;

        class {$name} extends {$extends}
        {
            public function index(Request \$request): Response
            {
                return \$this->view('{$this->viewPath($name)}/index.php', [
                    'titre' => '{$name}',
                ]);
            }
        }
        PHP;

        file_put_contents($path, $stub);
        echo "  [OK] src/Controllers/{$name}.php créé\n";
    }

    private function viewPath(string $name): string
    {
        return strtolower(str_replace(['Controller', 'ApiController'], '', $name));
    }
}
