<?php

namespace Core\Foundation;

class Config
{
    /** @var App $app */
    protected $app;
    /** @var array $config */
    protected $config;

    public function __construct(App $app)
    {
        $this->app = $app;

        $this->readFile();
    }

    /**
     * Retourne un élément de la configuration suivant sa clée
     *
     * @param string $key
     * @param mixed|null   $default
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        return array_key_exists($key, $this->config) ? $this->config[$key] : $default;
    }

    /**
     * Ajoute ou remplace un élément de la config
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function set(string $key, $value): self
    {
        $this->config[$key] = $value;

        return $this;
    }

    /**
     * Lit le fichier de config
     *
     * @return self
     */
    protected function readFile(): self
    {
        $this->config = require_once base_path('core/config.php');

        return $this;
    }
}
