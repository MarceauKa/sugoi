<?php

if (false === function_exists('dd')) {
    /**
     * Dump le contenu d'une ou plusieurs variables
     * et arrête l'éxecution du script
     */
    function dd(): void
    {
        echo '<pre>';
        var_dump(func_get_args());
        echo '</pre>';
        exit;
    }
}

if (false === function_exists('app')) {
    /**
     * Retourne une instance de la classe \Core\Foundation\App
     *
     * @param string|null $key
     * @return \Core\Foundation\App|mixed
     */
    function app(?string $key = null)
    {
        if (false === is_null($key)) {
            return \Core\Foundation\App::run()->instance($key);
        }

        return \Core\Foundation\App::run();
    }
}

if (false === function_exists('request')) {
    /**
     * Retourne une instance de la classe \Core\Http\Request
     *
     * @param string|null $key
     * @return \Core\Http\Request|mixed
     */
    function request(?string $key = null)
    {
        if (false === is_null($key)) {
            return app('request')->input($key);
        }

        return app('request');
    }
}

if (false === function_exists('response')) {
    /**
     * Retourne une instance de la classe \Core\Http\Response
     *
     * @return \Core\Http\Response
     */
    function response()
    {
        return app('response');
    }
}

if (false === function_exists('url')) {
    /**
     * Retourne une instance de la classe \Core\Http\Url
     *
     * @param string|null $path
     * @return \Core\Http\Url|mixed
     */
    function url(?string $path = null)
    {
        if (false === is_null($path)) {
            return app('url')->url($path);
        }

        return app('url');
    }
}

if (false === function_exists('escape')) {
    /**
     * Echappe une chaîne de caractère
     *
     * @param string|null $value
     * @return string|null
     */
    function escape(?string $value = null)
    {
        return empty($value) ? null : htmlspecialchars($value, ENT_QUOTES, 'UTF-8', true);
    }
}

if (false === function_exists('base_path')) {
    /**
     * Retourne un chemin depuis la racine du projet
     *
     * @param string|null $file
     * @return string
     */
    function base_path(?string $file = null)
    {
        $basePath = app()->basePath();

        if (false === is_null($file)) {
            return $basePath . $file;
        }

        return $basePath;
    }
}

if (false === function_exists('env')) {
    /**
     * Retourne une valeur d'environnement
     *
     * @param string $key
     * @param null   $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        return app('env')->get($key, $default);
    }
}

if (false === function_exists('config')) {
    /**
     * Retourne une instance de la classe \Core\Foundation\Config
     *
     * @param string|null $key
     * @param null        $default
     * @return \Core\Foundation\Config|mixed
     */
    function config(?string $key = null, $default = null)
    {
        if (false === is_null($key)) {
            return app('config')->get($key, $default);
        }

        return app('config');
    }
}

if (false === function_exists('db')) {
    /**
     * Retourne une instance de la classe \Core\Db\DB
     *
     * @return \Core\Db\DB
     */
    function db()
    {
        return app('db');
    }
}

if (false === function_exists('collect')) {
    /**
     * Retourne une collection depuis un tableau
     *
     * @param array $items
     * @return \Core\Foundation\Collection
     */
    function collect($items)
    {
        return new \Core\Foundation\Collection($items);
    }
}