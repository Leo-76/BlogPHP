<?php
namespace App\Core;

class Container
{
    private array $bindings  = [];
    private array $instances = [];

    public function singleton(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = ['factory' => $factory, 'singleton' => true];
    }

    public function bind(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = ['factory' => $factory, 'singleton' => false];
    }

    public function make(string $abstract): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (!isset($this->bindings[$abstract])) {
            throw new \RuntimeException("Service non enregistré : [{$abstract}]");
        }

        $binding  = $this->bindings[$abstract];
        $instance = ($binding['factory'])($this);

        if ($binding['singleton']) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }
}
