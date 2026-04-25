<?php
namespace App\Core;

class Config
{
    private array $data = [];

    public function __construct(string $configPath)
    {
        foreach (glob($configPath . '/*.php') as $file) {
            $key = basename($file, '.php');
            $this->data[$key] = require $file;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $data  = $this->data;
        foreach ($parts as $part) {
            if (!isset($data[$part])) return $default;
            $data = $data[$part];
        }
        return $data;
    }
}
