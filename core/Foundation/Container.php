<?php

namespace Core\Foundation;

class Container
{
    /** @var App $app */
    protected $app;
    /** @var array $bindings */
    protected $bindings = [];
    /** @var array $singletons */
    protected $singletons = [];
    /** @var array $aliases */
    protected $aliases = [];

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Retourne une instance depuis le nom d'une classe ou un alias
     *
     * @param string $class
     * @return mixed
     */
    public function resolve(string $class)
    {
        if (array_key_exists($class, $this->aliases)) {
            $class = $this->aliases[$class];
        }

        if (array_key_exists($class, $this->bindings)) {
            $className = $this->bindings[$class];

            return $className($this->app);
        }

        if (array_key_exists($class, $this->singletons)) {
            $singleton = $this->singletons[$class];

            if (is_null($singleton[1])) {
                $this->singletons[$class][1] = $singleton[0]($this->app);
                return $this->resolve($class);
            }

            return $singleton[1];
        }

        throw new \InvalidArgumentException("Aucun binding pour $class");
    }

    /**
     * Définit un alias pour une classe
     *
     * @param string $abstract
     * @param string $concrete
     * @return self
     */
    public function alias(string $abstract, string $concrete): self
    {
        if (false === array_key_exists($abstract, $this->aliases)) {
            $this->aliases[$abstract] = $concrete;
        }

        return $this;
    }

    /**
     * Enregistre un singleton dans le container
     *
     * @param string   $class
     * @param callable $constructor
     * @return self
     */
    public function singleton(string $class, callable $constructor): self
    {
        if (false === array_key_exists($class, $this->singletons)) {
            $this->singletons[$class] = [$constructor, null];
        }

        return $this;
    }

    /**
     * Enregistre une classe dans le container
     *
     * @param string   $class
     * @param callable $constructor
     * @return self
     */
    public function bind(string $class, callable $constructor): self
    {
        if (false === array_key_exists($class, $this->bindings)) {
            $this->bindings[$class] = $constructor;
        }

        return $this;
    }
}
