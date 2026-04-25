<?php
namespace App\Cache;

/**
 * Proxy autour de symfony/cache (PSR-16)
 * Identique à notre API maison
 */
class Cache
{
    private \Psr\SimpleCache\CacheInterface $store;

    public function __construct(\Psr\SimpleCache\CacheInterface $store)
    {
        $this->store = $store;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store->get($this->key($key), $default);
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        return $this->store->set($this->key($key), $value, $ttl);
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $k = $this->key($key);
        if ($this->store->has($k)) return $this->store->get($k);
        $value = $callback();
        $this->store->set($k, $value, $ttl);
        return $value;
    }

    public function forget(string $key): bool   { return $this->store->delete($this->key($key)); }
    public function has(string $key): bool       { return $this->store->has($this->key($key)); }
    public function flush(): bool                { return $this->store->clear(); }

    private function key(string $key): string
    {
        // Symfony cache n'accepte pas certains caractères
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
    }
}
