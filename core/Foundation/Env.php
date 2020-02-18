<?php

namespace Core\Foundation;

class Env
{
    /** @var App $app */
    protected $app;
    /** @var array $vars */
    protected $vars;

    public function __construct(App $app)
    {
        $this->app = $app;

        $this->extractVars();
    }

    /**
     * Retourne un élément de l'environnement
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return array_key_exists($key, $this->vars) ? $this->vars[$key] : $default;
    }

    /**
     * Lit le contenu du fichier d'environnement
     *
     * @return string
     */
    protected function getFile(): string
    {
        $file = base_path('.env');

        if (false === file_exists($file)) {
            throw new \RuntimeException("Le fichier .env n'a pas été créé");
        }

        return file_get_contents($file);
    }

    /**
     * Extrait les variables de configuration du fichier d'environnement
     *
     * @return self
     */
    protected function extractVars(): self
    {
        // Cette REGEX récupére : APP_VAR="ma variable"
        if (false != preg_match_all('/([a-z0-9_]+)=[\"\']?(.*)[\"\']?/iu', $this->getFile(), $matches)) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $this->vars[$matches[1][$i]] = $matches[2][$i];
            }
        }

        return $this;
    }
}
