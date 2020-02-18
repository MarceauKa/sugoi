<?php

namespace Core\Foundation;

class App
{
    const VERSION = '0.0.1';

    /** @var App $instance */
    protected static $instance;
    /** @var Container $container */
    protected $container;
    /** @var string $root */
    protected $root;

    private function __construct()
    {
        $this->container = new Container($this);

        $this->boot();
    }

    /**
     * Construit l'application via un singleton
     *
     * @return static
     */
    public static function run(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new static;
        }

        return self::$instance;
    }

    /**
     * Retourne le chemin vers un fichier depuis la racine du site
     *
     * @return string
     */
    public function basePath(): string
    {
        if (is_null($this->root)) {
            $this->root = dirname(__DIR__, 2) . '/';
        }

        return $this->root;
    }

    /**
     * Récupère une instance depuis le container
     *
     * @param string $class
     * @return mixed
     */
    public function instance(string $class)
    {
        return $this->container->resolve($class);
    }

    /**
     * Enregistre les dépendances dans le container
     *
     * @return self
     */
    protected function boot(): self
    {
        $this->container
            ->singleton(Env::class, function ($app) {
                return new Env($app);
            })
            ->singleton(Config::class, function ($app) {
                return new Config($app);
            })
            ->singleton(DB::class, function (App $app) {
                return new DB($app->instance('config'));
            })
            ->singleton(Request::class, function () {
                return new Request();
            })
            ->singleton(Response::class, function (App $app) {
                return new Response($app->instance(Request::class));
            })
            ->singleton(Router::class, function (App $app) {
                return new Router($app->instance(Request::class));
            })
            ->singleton(Url::class, function (App $app) {
                return new Url($app->instance(Request::class), $app->instance(Router::class));
            })
            ->bind(Oops::class, function (App $app) {
                return new Oops($app->instance(Response::class));
            })
            ->alias('env', Env::class)
            ->alias('config', Config::class)
            ->alias('db', DB::class)
            ->alias('request', Request::class)
            ->alias('response', Response::class)
            ->alias('router', Router::class)
            ->alias('url', Url::class);

        return $this;
    }
}
