<?php

namespace Core\Foundation;

use Core\Cli\Cli;
use Core\Cli\Console;
use Core\Db\DB;
use Core\Error\Oops;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Url;
use Core\Router\Router;

abstract class App
{
    const VERSION = '0.0.1';

    /** @var App $instance */
    protected static $instance;
    /** @var Container $container */
    protected $container;
    /** @var string $root */
    protected $root;
    /** @var bool $runningInConsole */
    protected $runningInConsole = false;

    private function __construct()
    {
        $this->container = new Container($this);

        $this->boot();
        $this->register();
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
            ->singleton(Cli::class, function ($app) {
                return new Cli($app);
            })
            ->singleton(Console::class, function ($app) {
                return new Console($app);
            })
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
            ->alias('cli', Cli::class)
            ->alias('console', Console::class)
            ->alias('env', Env::class)
            ->alias('config', Config::class)
            ->alias('db', DB::class)
            ->alias('request', Request::class)
            ->alias('response', Response::class)
            ->alias('router', Router::class)
            ->alias('url', Url::class);

        return $this;
    }

    /**
     * Register all app component (commands, ...)
     *
     * @return self
     */
    protected function register(): self
    {
        $this->registerSingletons($this->container)
             ->registerInstances($this->container)
             ->registerCommands($this->instance('console'));

        return $this;
    }

    /**
     * Enregistre des commandes pour la console
     *
     * @return self
     */
    abstract public function registerCommands(Console $console): self;

    /**
     * Enregistre les instances pour le container
     *
     * @return self
     */
    abstract public function registerInstances(Container $container): self;

    /**
     * Enregistre les instances pour le container
     *
     * @return self
     */
    abstract public function registerSingletons(Container $container): self;

    /**
     * Retourne si l'application tourne en CLI
     * et permet de définir ce statut si un paramètre booléen est fourni
     *
     * @param bool|null $status
     * @return bool
     */
    public function runningInConsole(?bool $status = null): bool
    {
        if (is_null($status)) {
            return $this->runningInConsole;
        }

        $this->runningInConsole = $status;

        return $status;
    }
}
