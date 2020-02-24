<?php

namespace Core\Cli\Commands;

use Core\Cli\Cli;
use Core\Cli\Exceptions\InvalidCommandException;
use Core\Foundation\App;

abstract class BaseCommand
{
    /** @var App $app */
    protected $app;
    /** @var Cli $cli */
    protected $cli;

    /**
     * Execute la commande
     *
     * @return void
     */
    abstract public function handle(): void;

    /**
     * Injecte l'application et le CLI dans la commande
     *
     * @param App $app
     * @return self
     */
    public function setApp(App $app): self
    {
        $this->app = $app;
        $this->cli = $app->instance('cli');

        return $this;
    }

    /**
     * Vérifie si la commande porte le nom fourni en paramètre
     *
     * @param string $name
     * @return bool
     * @throws InvalidCommandException
     */
    public function match(string $name): bool
    {
        return $name === $this->getName();
    }

    /**
     * Retourne le nom de la commande
     *
     * @return string
     * @throws InvalidCommandException
     */
    public function getName(): string
    {
        if (property_exists($this, 'name')) {
            return $this->name;
        }

        throw new InvalidCommandException(sprintf("La commande %s n'a pas de nom", get_class($this)));
    }

    /**
     * Retourne la description de la commande
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return property_exists($this, 'description') ? $this->description : null;
    }
}
