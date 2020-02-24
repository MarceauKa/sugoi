<?php

namespace App;

use Core\Cli\Console;
use Core\Foundation\App;
use Core\Foundation\Container;

class Application extends App
{
    public function registerCommands(Console $console): App
    {
        return $this;
    }

    public function registerInstances(Container $container): App
    {
        return $this;
    }

    public function registerSingletons(Container $container): App
    {
        return $this;
    }
}
