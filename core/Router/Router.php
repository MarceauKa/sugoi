<?php

namespace Core\Router;

use Core\Http\Request;
use Core\Http\Response;
use Core\Router\Exceptions\RouteNotFoundException;

class Router
{
    /** @var Request $request */
    protected $request;
    /** @var array $routes */
    protected $routes = [];
    /** @var array $group */
    protected $group = [
        // Prefixe de l'URI. Ex : admin/
        'prefix' => null,
        // Prefixe du nom de la route. Ex : admin.
        'as' => null,
        // Middlewares à appliquer au groupe
        'middleware' => [],
    ];

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->mapRoutes();
    }

    /**
     * Ecoute la requête entrante et retourne une instance de Response
     *
     * @return Response
     */
    public function listen(): Response
    {
        $uri = $this->request->server('PATH_INFO') ?? '/';
        $method = $this->request->server('REQUEST_METHOD');

        foreach ($this->routes as $route) {
            if ($route->match($method, $uri)) {
                return $route->response();
            }
        }

        throw new RouteNotFoundException("Aucune route ne matche pour $uri");
    }

    /**
     * Lit les routes depuis le fichier routes.php
     *
     * @return self
     */
    public function mapRoutes(): self
    {
        $router = $this;

        require_once base_path('app/routes.php');

        return $this;
    }

    /**
     * Crée un nouveau groupe de route
     *
     * @param array    $params
     * @param callable $callback
     * @return $this
     */
    public function group(array $params, callable $callback): self
    {
        $prefix = array_key_exists('prefix', $params) ? trim($params['prefix'], '\\/') : null;
        $as = array_key_exists('as', $params) ? trim($params['as']) : null;
        $middleware = array_key_exists('middleware', $params) ? $params['middleware'] : [];

        $this->group = [
            'prefix' => $prefix,
            'as' => $as,
            'middleware' => is_array($middleware) ? $middleware : [$middleware],
        ];

        $callback($this);

        $this->group = [
            'prefix' => null,
            'as' => null,
            'middleware' => [],
        ];

        return $this;
    }

    /**
     * Retourne une route depuis son nom
     *
     * @param string $name
     * @return Route|null
     */
    public function named(string $name): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->isNamed($name)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Ajoute une nouvelle route de type GET
     *
     * @see addRoute
     */
    public function get(string $uri, string $action, ?string $name = null): self
    {
        return $this->addRoute('GET', $uri, $action, $name);
    }

    /**
     * Ajoute une nouvelle route de type POST
     *
     * @see addRoute
     */
    public function post(string $uri, string $action, ?string $name = null): self
    {
        return $this->addRoute('POST', $uri, $action, $name);
    }

    /**
     * Enregistre une nouvelle route au Router
     *
     * @param string      $method La méthode (GET, POST, etc)
     * @param string      $uri L'URI de la route
     * @param string      $action L'action de la route au format Controller@Method depuis le chemin App\Controllers
     * @param string|null $name Le nom souhaité pour la route
     * @return self
     */
    protected function addRoute(string $method, string $uri, string $action, ?string $name = null): self
    {
        $uri = $this->parseUri($uri);
        $name = $this->parseName($name);
        $key = sprintf('%s@%s', $method, $uri);

        if (false === array_key_exists($key, $this->routes)) {
            $actionParts = explode('@', $action);

            if (count($actionParts) != 2) {
                throw new \InvalidArgumentException("L'action de la route $uri est invalide");
            }

            $this->routes[$key] = new Route($method, $uri, $actionParts[0], $actionParts[1]);

            if (false === is_null($name)) {
                $this->routes[$key]->setName($name);
            }
        }

        return $this;
    }

    /**
     * Parse l'URI d'une route. Ajoute le prefixe d'URI de groupe si présent.
     *
     * @param string $uri
     * @return string
     */
    protected function parseUri(string $uri): string
    {
        if (! empty($this->group['prefix'])) {
            return '/' . trim($this->group['prefix'], '/') . '/' . ltrim($uri, '/');
        }

        return $uri ?? '/';
    }

    /**
     * Parse le nom d'une route. Ajoute le prefixe de nom de groupe si présent.
     *
     * @param string|null $name
     * @return string|null
     */
    protected function parseName(?string $name = null): ?string
    {
        if (empty($name)) {
            return null;
        }

        return ! empty($this->group['as'])
            ? $this->group['as'] . $name
            : $name;
    }

    /**
     * @todo
     * @param array $middleware
     * @return array
     */
    protected function parseMiddleware(array $middleware = []): array
    {
        return $middleware;
    }
}
