<?php

namespace Core\Cli\Commands;

use Core\Foundation\App;

class HelpCommand extends BaseCommand
{
    /** @var string $name */
    public $name = 'help';
    /** @var string $description */
    public $description = "Affiche l'aide et la liste des commandes";

    public function handle(): void
    {
        $commands = $this->app->instance('console')->getCommands();

        $this->cli
            ->info(sprintf('Sugoi v%s', App::VERSION))
            ->newLine()
            ->write('Liste des commandes disponibles :')
            ->newLine();

        foreach ($commands as $command) {
            /** @var BaseCommand $command */
            $command = new $command;

            $this->cli->write(vsprintf('%s %s', [
                $this->cli->color($command->getName(), 'yellow'),
                $command->getDescription(),
            ]));
        }
    }
}
