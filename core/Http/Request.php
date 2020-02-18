<?php

namespace Core\Http;

class Request
{
    /** @var array $get */
    protected $get;
    /** @var array $post */
    protected $post;
    /** @var array $server */
    protected $server;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
    }

    /**
     * Récupére un élément de $_GET ou $_POST depuis sa clé
     * ou retourne tous les éléments si aucune clé n'est fournie
     *
     * @param string|null $key
     * @return array|mixed|null
     */
    public function input(?string $key = null)
    {
        $data = array_merge($this->get, $this->post);

        if (is_null($key)) {
            return $data;
        }

        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        return null;
    }

    /**
     * Récupére un élément de $_SERVER depuis sa clé
     * ou retourne tous les éléments si aucune clé n'est fournie
     *
     * @param string|null $key
     * @return array|mixed|null
     */
    public function server(?string $key = null)
    {
        if (is_null($key)) {
            return $this->server;
        }

        if (array_key_exists($key, $this->server)) {
            return $this->server[$key];
        }

        return null;
    }
}
