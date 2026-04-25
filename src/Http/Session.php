<?php
namespace App\Http;

use Symfony\Component\HttpFoundation\Session\Session as SfSession;

/**
 * Proxy autour de symfony/http-foundation Session
 * API identique à notre Session maison
 */
class Session extends SfSession
{
    // ── Flash messages compatibles ──────────────────────────────
    public function flash(string $key, mixed $value): void
    {
        $this->getFlashBag()->set($key, is_array($value) ? $value : [$value]);
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        $flashes = $this->getFlashBag()->get($key, []);
        return empty($flashes) ? $default : (count($flashes) === 1 ? $flashes[0] : $flashes);
    }

    public function hasFlash(string $key): bool
    {
        return $this->getFlashBag()->has($key);
    }

    // ── Compatibilité notre API maison ──────────────────────────
    public function isAuthenticated(): bool { return $this->has('user_id'); }
    public function regenerate(): void      { $this->migrate(true); }
    public function destroy(): void         { $this->invalidate(); }
}
