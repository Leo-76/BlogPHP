<?php
use App\Core\Application;
use Symfony\Component\HttpFoundation\Response as SfResponse;
use Symfony\Component\HttpFoundation\Session\Session as SfSession;
use App\Auth\Auth;

function app(?string $abstract = null): mixed
{
    $instance = Application::getInstance();
    return $abstract ? $instance->make($abstract) : $instance;
}

function env(string $key, mixed $default = null): mixed { return $_ENV[$key] ?? $_SERVER[$key] ?? $default; }
function config(string $key, mixed $default = null): mixed { return app('config')->get($key, $default); }
function view(string $template, array $data = []): string { return app('view')->render($template, $data); }

function redirect(string $url, int $status = 302): \Symfony\Component\HttpFoundation\RedirectResponse
{
    return new \Symfony\Component\HttpFoundation\RedirectResponse($url, $status);
}
function back(): \Symfony\Component\HttpFoundation\RedirectResponse
{
    return redirect($_SERVER['HTTP_REFERER'] ?? '/');
}

// Session Symfony
function session(): \App\Http\Session { return app('session'); }

// Auth
function auth_user(): ?array  { return Auth::user(); }
function auth_check(): bool   { return Auth::check(); }
function auth_guest(): bool   { return Auth::guest(); }
function auth_admin(): bool   { return Auth::isAdmin(); }

// Cache Symfony (PSR-16)
function cache(): \App\Cache\Cache { return app('cache_proxy'); }

// Mailer Symfony
function mailer(): \App\Mail\SymfonyMailer { return app('mailer'); }

// Password hasher Symfony
function sf_bcrypt(string $value): string
{
    return app('hasher')->hash($value);
}
function sf_hash_check(string $value, string $hash): bool
{
    return app('hasher')->verify($hash, $value);
}

// DB DBAL
function db(): \Doctrine\DBAL\Connection { return app('db'); }

// Validation
function validate(array $data, array $rules, array $messages = []): \App\Http\Validation\Validator
{
    return \App\Http\Validation\Validator::make($data, $rules, $messages);
}

// CSRF
function csrf_token(): string
{
    if (!session()->has('_csrf')) session()->set('_csrf', bin2hex(random_bytes(32)));
    return session()->get('_csrf');
}
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">'; }

// Formulaires
function old(string $key, string $default = ''): string
{
    $old = session()->getFlashBag()->peek('_old')[0] ?? [];
    return htmlspecialchars($old[$key] ?? $default);
}
function errors(?string $field = null): mixed
{
    $errs = session()->getFlashBag()->peek('errors')[0] ?? [];
    return $field ? ($errs[$field][0] ?? null) : $errs;
}
function has_error(string $field): bool
{
    return !empty(session()->getFlashBag()->peek('errors')[0][$field]);
}

// Chemins
function base_path(string $path = ''): string { return app()->path($path); }
function asset(string $path): string { return '/' . ltrim($path, '/'); }
function upload_url(string $path): string { return '/uploads/' . ltrim($path, '/'); }

// Logs
function log_info(string $msg, array $ctx = []): void  { app('log')->info($msg, $ctx); }
function log_error(string $msg, array $ctx = []): void { app('log')->error($msg, $ctx); }
function log_debug(string $msg, array $ctx = []): void { app('log')->debug($msg, $ctx); }

// Utilitaires
function slug(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = str_replace(['à','â','ä','é','è','ê','ë','î','ï','ô','ö','ù','û','ü','ç','æ','œ'],
                        ['a','a','a','e','e','e','e','i','i','o','o','u','u','u','c','ae','oe'], $text);
    return preg_replace('/[\s-]+/', '-', trim(preg_replace('/[^a-z0-9\s-]/', '', $text)));
}
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES); }
function dd(mixed ...$vars): void
{
    echo '<style>pre{background:#1e1e2e;color:#cdd6f4;padding:1rem;border-radius:8px;overflow:auto}</style>';
    foreach ($vars as $v) { echo '<pre>'; var_dump($v); echo '</pre>'; }
    die();
}
function abort(int $code, string $message = ''): void { throw new \App\Exceptions\HttpException($code, $message); }
function now(): string   { return date('Y-m-d H:i:s'); }
function today(): string { return date('Y-m-d'); }
function paginate(string $sql, array $params = [], int $perPage = 10): \App\Database\Paginator
{
    return \App\Database\Paginator::paginate($sql, $params, $perPage);
}
