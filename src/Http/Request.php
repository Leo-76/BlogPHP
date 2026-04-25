<?php
namespace App\Http;

use Symfony\Component\HttpFoundation\Request as SfRequest;

/**
 * Étend la vraie Request Symfony — toutes les méthodes Symfony disponibles
 */
class Request extends SfRequest
{
    public array $params = [];

    public static function capture(): static
    {
        $instance = static::createFromGlobals();
        return $instance;
    }

    // Raccourcis pratiques
    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->request->get($key) ?? $this->query->get($key) ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->request->get($key, $default);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query->get($key, $default);
    }

    public function isPost(): bool { return $this->isMethod('POST'); }
    public function isGet(): bool  { return $this->isMethod('GET'); }

    public function all(): array
    {
        return array_merge($this->query->all(), $this->request->all());
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function bearerToken(): ?string
    {
        $auth = $this->headers->get('Authorization', '');
        return str_starts_with($auth, 'Bearer ') ? substr($auth, 7) : null;
    }

    public function hasFile(string $key): bool
    {
        return $this->files->has($key) && $this->files->get($key)?->isValid();
    }

    public function file(string $key): ?UploadedFile
    {
        $sfFile = $this->files->get($key);
        if (!$sfFile || !$sfFile->isValid()) return null;
        return new UploadedFile(
            $sfFile->getClientOriginalName(),
            $sfFile->getPathname(),
            $sfFile->getMimeType() ?? '',
            $sfFile->getSize(),
            $sfFile->getError()
        );
    }

    public function json(): array
    {
        return json_decode($this->getContent(), true) ?? [];
    }

    /**
     * Validation avec symfony/validator
     */
    public function validate(array $rules, array $messages = []): \App\Http\Validation\Validator
    {
        $v = \App\Http\Validation\Validator::make($this->all(), $rules, $messages);
        if ($v->fails()) {
            session()->getFlashBag()->set('errors', $v->errors());
            session()->getFlashBag()->set('_old', $this->request->all());
        }
        return $v;
    }
}
