<?php

namespace Core\Cli;

use Core\Cli\Commands\BaseCommand;
use Core\Cli\Exceptions\CommandNotFoundException;
use Core\Foundation\App;

class Console
{
    /** @var App $app */
    protected $app;
    /** @var string $default */
    protected $default = 'help';
    /** @var array $commands */
    protected $commands = [];
    /** @var string $command */
    protected $command;
    /** @var array $args */
    protected $args;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->app->runningInConsole(true);

        $this->setCommandFromArgs();
        $this->boot();
    }

    /**
     * Ecoute la commande entrante
     *
     * @throws CommandNotFoundException
     * @throws Exceptions\InvalidCommandException
     */
    public function listen(): void
    {
        foreach ($this->commands as $command) {
            /** @var BaseCommand $command */
            $command = new $command;

            if (false === $command instanceof BaseCommand) {
                continue;
            }

            if ($command->match($this->command)) {
                $command->setApp($this->app)->handle();
                return;
            }
        }

        throw new CommandNotFoundException(sprintf("La commande %s n'existe pas", $this->command));
    }

    /**
     * Retourne la liste des commandes
     *
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Récupére la commande et les options depuis
     * la variable globale $argv
     *
     * @return self
     */
    protected function setCommandFromArgs(): self
    {
        $args = collect($_SERVER['argv']);

        $this->command = $args->slice(1, 1)->first() ?: $this->default;
        $this->args = $args->slice(2, $args->count() - 1)->all();

        return $this;
    }

    /**
     * @return $this
     */
    protected function boot(): self
    {
        $this->commands = [];
        $this->commands[] = \Core\Cli\Commands\HelpCommand::class;
        $this->commands[] = \Core\Cli\Commands\ServeCommand::class;

        return $this;
    }
}
