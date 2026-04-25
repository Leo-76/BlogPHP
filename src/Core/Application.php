<?php
namespace App\Core;

use Symfony\Component\HttpFoundation\Request as SfRequest;
use Symfony\Component\HttpFoundation\Response as SfResponse;
use Symfony\Component\HttpFoundation\Session\Session as SfSession;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\Validator\Validation;
use Doctrine\DBAL\DriverManager;
use App\Exceptions\HttpException;

class Application
{
    private static Application $instance;
    private Container $container;
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath   = rtrim($basePath, '/');
        $this->container  = new Container();
        static::$instance = $this;
        $this->bootstrap();
    }

    public static function getInstance(): static { return static::$instance; }

    private function bootstrap(): void
    {
        $this->loadEnv();
        $this->ensureDirectories();
        $base = $this->basePath;

        // ── symfony/http-foundation — Session ───────────────────
        $this->container->singleton('session', function() use ($base) {
            $handler = new NativeFileSessionHandler("{$base}/storage/sessions");
            $storage = new NativeSessionStorage(['cookie_httponly' => true, 'cookie_samesite' => 'lax'], $handler);
            $session = new SfSession($storage);
            $session->start();
            return $session;
        });

        // ── doctrine/dbal — Connexion BDD ───────────────────────
        $this->container->singleton('db', function() use ($base) {
            $driver = env('DB_CONNECTION', 'sqlite');
            $params = $driver === 'sqlite'
                ? ['driver' => 'pdo_sqlite', 'path' => "{$base}/" . env('DB_DATABASE', 'database/database.sqlite')]
                : [
                    'driver'   => 'pdo_mysql',
                    'host'     => env('DB_HOST', '127.0.0.1'),
                    'port'     => (int)env('DB_PORT', 3306),
                    'dbname'   => env('DB_DATABASE', 'blog'),
                    'user'     => env('DB_USERNAME', 'root'),
                    'password' => env('DB_PASSWORD', ''),
                    'charset'  => 'utf8mb4',
                ];
            return DriverManager::getConnection($params);
        });

        // ── symfony/cache — Cache PSR-16 ────────────────────────
        $this->container->singleton('cache', function() use ($base) {
            $adapter = new FilesystemAdapter('blogphp', 0, "{$base}/storage/cache");
            return new Psr16Cache($adapter);
        });

        // ── symfony/password-hasher ─────────────────────────────
        $this->container->singleton('hasher', fn() => new NativePasswordHasher());

        // ── symfony/validator ───────────────────────────────────
        $this->container->singleton('validator', fn() => Validation::createValidator());

        // ── symfony/mailer ──────────────────────────────────────
        $this->container->singleton('mailer', fn() => new \App\Mail\SymfonyMailer());

        // ── symfony/routing ─────────────────────────────────────
        $this->container->singleton('routes',  fn() => new RouteCollection());
        $this->container->singleton('router',  fn() => new \App\Http\Router());

        // ── Classes maison (View, Log, Config) ──────────────────
        $this->container->singleton('config', fn() => new Config("{$base}/config"));
        $this->container->singleton('view',   fn() => new \App\View\TwigEngine("{$base}/resources/views"));
        $this->container->singleton('log',    fn() => new Logger("{$base}/storage/logs"));

        $this->autoMigrate();
    }

    private function loadEnv(): void
    {
        $file = $this->basePath . '/.env';
        if (!file_exists($file)) return;
        if (class_exists(\Dotenv\Dotenv::class)) {
            \Dotenv\Dotenv::createImmutable($this->basePath)->safeLoad();
            return;
        }
        foreach (file($file) as $line) {
            $line = trim($line);
            if (!$line || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$k, $v] = explode('=', $line, 2);
            $_ENV[trim($k)] = $_SERVER[trim($k)] = trim(trim($v), '"\'');
        }
    }

    private function ensureDirectories(): void
    {
        foreach (['storage/logs','storage/cache','storage/sessions','database','public/uploads'] as $dir) {
            $path = $this->basePath . '/' . $dir;
            if (!is_dir($path)) mkdir($path, 0755, true);
        }
    }

    private function autoMigrate(): void
    {
        try {
            $conn = $this->container->make('db');
            $conn->executeStatement("CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT NOT NULL,
                run_at TEXT DEFAULT (datetime('now'))
            )");
            $path = $this->basePath . '/database/migrations';
            if (!is_dir($path)) return;
            $files = glob($path . '/*.php');
            sort($files);
            foreach ($files as $file) {
                $name = basename($file);
                $done = $conn->fetchOne("SELECT COUNT(*) FROM migrations WHERE migration=?", [$name]);
                if ($done) continue;
                (require $file)->up($conn);
                $conn->executeStatement("INSERT INTO migrations (migration) VALUES (?)", [$name]);
            }
        } catch (\Throwable $e) { error_log('Migration: ' . $e->getMessage()); }
    }

    public function run(): void
    {
        try {
            $sfRequest = SfRequest::createFromGlobals();
            $router    = $this->make('router');

            require $this->basePath . '/routes/web.php';

            $response = $router->dispatch($sfRequest);
            $response->send();

        } catch (HttpException $e) {
            $r = new SfResponse('', $e->getStatusCode());
            try { $r = new SfResponse($this->make('view')->render("errors/{$e->getStatusCode()}.php", []), $e->getStatusCode()); }
            catch (\Throwable) {}
            $r->send();
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $body = env('APP_DEBUG', 'false') === 'true'
                ? "<style>body{font-family:monospace;padding:2rem;background:#1e1e2e;color:#cdd6f4}</style><h2 style='color:#f38ba8'>" . htmlspecialchars($e->getMessage()) . "</h2><pre style='color:#a6e3a1'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>"
                : "<h1>500 — Erreur serveur</h1>";
            (new SfResponse($body, 500))->send();
        }
    }

    public function make(string $abstract): mixed { return $this->container->make($abstract); }
    public function path(string $path = ''): string { return $this->basePath . ($path ? '/' . ltrim($path, '/') : ''); }
}
