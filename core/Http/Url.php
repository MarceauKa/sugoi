<?php

namespace Core\Http;

use App\Core\Exceptions\RouteNotFoundException;

class Url
{
    /** @var Request $request */
    protected $request;
    /** @var string $base */
    protected $base;
    /** @var bool $secure */
    protected $secure = false;

    public function __construct(Request $request, Router $router)
    {
        $this->request = $request;
        $this->router = $router;
        $this->base = trim($request->server('HTTP_HOST'), '/');
    }

    /**
     * Retourne une URL vers un des fichiers statique contenu dans public/
     *
     * @param string $path
     * @return string
     */
    public function url(string $path): string
    {
        return vsprintf('%s://%s/%s', [
            $this->secure ? 'https' : 'http',
            $this->base,
            trim($path, '/')
        ]);
    }

    /**
     * Retourne une URL pour une route donnée
     *
     * @param string     $name
     * @param array|null $params
     * @return string
     */
    public function route(string $name, ?array $params = null): string
    {
        $route = $this->router->named($name);

        if ($route instanceof Route) {
            return $this->url($route->uri($params));
        }

        throw new RouteNotFoundException("Aucune route nommée $name");
    }
}
