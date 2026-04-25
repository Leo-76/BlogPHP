<?php
define('BASE_PATH', dirname(__DIR__));

if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
} else {
    // Autoloader PSR-4 maison (sans composer install)
    spl_autoload_register(function (string $class) {
        if (str_starts_with($class, 'App\\')) {
            $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
            if (file_exists($file)) require $file;
        }
    });
    // .env maison
    if (file_exists(BASE_PATH . '/.env')) {
        foreach (file(BASE_PATH . '/.env') as $line) {
            $line = trim($line);
            if (!$line || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$k, $v] = explode('=', $line, 2);
            $_ENV[trim($k)] = $_SERVER[trim($k)] = trim(trim($v), '"\'');
        }
    }
    // Helpers globaux
    require BASE_PATH . '/src/Core/helpers.php';
}

$app = new App\Core\Application(BASE_PATH);
$app->run();
