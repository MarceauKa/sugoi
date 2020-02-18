<?php

namespace Core\Router;

class Route
{
    /** @var string $method */
    protected $method;
    /** @var string $uri */
    protected $uri;
    /** @var string $controller */
    protected $controller;
    /** @var string $action */
    protected $action;
    /** @var string $name */
    protected $name;
    /** @var array $params */
    protected $params = [];
    /** @var string $regex */
    protected $regex;

    public function __construct(string $method, string $uri, string $controller, string $action)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->controller = 'App\\Controllers\\' . $controller;
        $this->action = $action;

        $this->bindParams();
    }

    /**
     * Vérifie que la route matche avec les infos HTTP fournies
     *
     * @param string $method
     * @param string $uri
     * @return bool
     */
    public function match(string $method, string $uri): bool
    {
        if ($method !== $this->method) {
            return false;
        }

        if (false === $this->compareUri($uri)) {
            return false;
        }

        return true;
    }

    /**
     * Prépare la réponse de la route
     * et injecte les paramètres demandés par l'action de la route
     *
     * @return Response
     * @throws \ReflectionException
     */
    public function response(): Response
    {
        $reflection = new \ReflectionClass($this->controller);
        $params = [];

        // Ceci permet de récupérer les paramètres (et le type) de la signature de la méthode de la route
        foreach ($reflection->getMethod($this->action)->getParameters() as $param) {
            $params[$param->getName()] = $param->getClass() ? $param->getClass()->name : null;
        }

        // Prépare les variables pour les injecter dans l'appel de la route
        foreach ($params as $key => $value) {
            // Paramètre de route. Ex : /ma-route/{name} => action(string $name)
            if (array_key_exists($key, $this->params)) {
                $params[$key] = $this->params[$key];
                continue;
            }

            // Paramètre de container. Ex : App\Core\Request => action(App\Core\Request $request)
            if (false === is_null($value)) {
                try {
                    $instance = app($value);
                } catch (\Exception $e) {
                    continue;
                }

                $params[$key] = $instance;
            }
        }

        $class = $this->controller;
        return call_user_func_array([new $class, $this->action], $params);
    }

    /**
     * Compare l'URI de la requête à la route
     *
     * @param string $uri
     * @return bool
     */
    protected function compareUri(string $uri): bool
    {
        if (false != preg_match($this->regex, $uri, $matches)) {
            $this->extractParams($uri);
            return true;
        }

        return false;
    }

    /**
     * Prépare les paramètres demandés par la route
     *
     * @return self
     */
    protected function bindParams(): self
    {
        $this->regex = sprintf('/^%s$/iu', str_replace('/', '\/', $this->uri));

        if (false != preg_match_all('/(\{\w+\})/iu', $this->uri, $matches)) {
            foreach ($matches[0] as $index => $param) {
                $this->regex = str_replace($param, '(\w+)', $this->regex);
                $param = str_replace(['{', '}'], '', $param);
                $this->params[$param] = null;
            }
        }

        return $this;
    }

    /**
     * Extrait de l'URI les paramètres demandés par la route
     *
     * @param string $uri
     * @return self
     */
    protected function extractParams(string $uri): self
    {
        if (false != preg_match_all($this->regex, $uri, $matches)) {
            array_shift($matches);
            $index = 0;

            foreach ($this->params as $key => $param) {
                $this->params[$key] = $matches[$index][0];
                $index++;
            }
        }

        return $this;
    }

    /**
     * Donne un route à la route
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Vérifie si la route s'appelle par le nom fourni
     *
     * @param string $name
     * @return bool
     */
    public function isNamed(string $name): bool
    {
        return $this->name === $name;
    }

    /**
     * Retourne l'URI de la route avec les paramètres fournis
     *
     * @param array|null $params
     * @return string
     */
    public function uri(?array $params = []): string
    {
        if ($this->params) {
            return str_replace(
                array_map(function ($item) {
                    return sprintf('{%s}', $item);
                }, array_keys($this->params)),
                array_values($params),
                $this->uri
            );
        }

        return $this->uri;
    }
}
