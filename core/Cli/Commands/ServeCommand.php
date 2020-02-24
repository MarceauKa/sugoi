<?php

namespace Core\Cli\Commands;

class ServeCommand extends BaseCommand
{
    /** @var string DEFAULT_HOST */
    const DEFAULT_HOST = '127.0.0.1';
    /** @var int DEFAULT_PORT */
    const DEFAULT_PORT = 8080;
    /** @var string $name */
    public $name = 'serve';
    /** @var string $description */
    public $description = "Sert l'application avec le serveur HTTP interne";

    public function handle(): void
    {
        $this->cli->info(sprintf("L'application écoute à l'adresse %s:%d", self::DEFAULT_HOST, self::DEFAULT_PORT));

        exec(
            vsprintf(
                'php -S %s:%s -t public',
                [
                    self::DEFAULT_HOST,
                    self::DEFAULT_PORT,
                ]
            )
        );
    }
}
