<?php

namespace Core\Http;

use Core\Foundation\App;

class Response
{
    /** @var Request $request */
    protected $request;
    /** @var Response|string $body */
    protected $body;
    /** @var array $headers */
    protected $headers = [];

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->withHeader('Version', sprintf('Sugoi %s', App::VERSION));
    }

    /**
     * Ajoute un header HTTP à la réponse qui sera renvoyée
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function withHeader(string $key, $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Définit une réponse de type redirection vers la requête fournie
     *
     * @param string $url
     * @return self
     */
    public function redirect(string $url): self
    {
        return $this->withHeader('Location', $url);
    }

    /**
     * Définit une réponse de type vue HTML avec les paramètres fournis
     *
     * @param string $name
     * @param array  $params
     * @return self
     */
    public function view(string $name, array $params = []): self
    {
        $this->body = new View($name, $params);

        return $this;
    }

    /**
     * Retourne le contenu de la réponse pour envoi au navigateur
     *
     * @return string|null
     */
    public function send(): ?string
    {
        $this->appendHeaders();

        if ($this->body instanceof View) {
            return (string)$this->body;
        }

        return null;
    }

    /**
     * Ajoute les headers configurés à la réponse HTTP
     *
     * @return self
     */
    protected function appendHeaders(): self
    {
        foreach ($this->headers as $key => $value) {
            header(sprintf('%s: %s', $key, $value));
        }

        return $this;
    }
}
