<?php

namespace Core\Cli;

use Core\Cli\Exceptions\CliError;
use Core\Foundation\App;

class Cli
{
    /** @var App $app */
    protected $app;
    /** @var array $colors */
    protected $colors = [
        'black'        => '0;30',
        'blue'         => '0;34',
        'green'        => '0;32',
        'cyan'         => '0;36',
        'red'          => '0;31',
        'purple'       => '0;35',
        'yellow'       => '1;33',
        'white'        => '1;37',
    ];
    /** @var array $backgrounds */
    protected $backgrounds = [
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
    ];

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param string      $text
     * @param string      $color
     * @param string|null $background
     * @throws CliError
     */
    public function write(string $text, string $color = 'white', ?string $background = null): self
    {
        fwrite(STDOUT, $this->color($text, $color, $background) . PHP_EOL);

        return $this;
    }

    /**
     * Affiche un message de succès (blanc sur vert)
     *
     * @param string $text
     * @throws CliError
     */
    public function success(string $text): self
    {
        $this->write($text, 'white', 'green');

        return $this;
    }

    /**
     * Affiche un message d'avertissement (noir sur jaune)
     *
     * @param string $text
     * @throws CliError
     */
    public function warning(string $text): self
    {
        $this->write($text, 'black', 'yellow');

        return $this;
    }

    /**
     * Affiche un message d'erreur (blanc sur rouge)
     *
     * @param string $text
     * @throws CliError
     */
    public function error(string $text): self
    {
        $this->write($text, 'white', 'red');

        return $this;
    }

    /**
     * Affiche un message d'info (blanc sur bleu)
     *
     * @param string $text
     * @throws CliError
     */
    public function info(string $text): self
    {
        $this->write($text, 'white', 'blue');

        return $this;
    }

    /**
     * Affiche une nouvelle ligne
     *
     * @throws CliError
     */
    public function newLine(): self
    {
        $this->write(' ');

        return $this;
    }

    /**
     * Pose une question à l'utilisateur et retourne le résultat
     *
     * @param string $question
     * @param null   $default
     * @return string|null
     */
    public function ask(string $question, $default = null): ?string
    {
        $this->warning($question);

        return $this->input() ?? $default;
    }

    /**
     * Récupère la saisie utilisateur
     *
     * @return string|null
     */
    public function input(): ?string
    {
        return trim(fgets(STDIN));
    }

    /**
     * Retourne un texte formaté en couleur
     *
     * @param string      $text
     * @param string      $color
     * @param string|null $background
     * @return string
     * @throws CliError
     */
    public function color(string $text, string $color = 'white', ?string $background = null): string
    {
        if (false === array_key_exists($color, $this->colors)) {
            throw new CliError(sprintf("La couleur %s n'existe pas", $color));
        }

        if (false === is_null($background) && false === array_key_exists($background, $this->backgrounds)) {
            throw new CliError(sprintf("Le fond %s n'existe pas", $background));
        }

        return vsprintf("\033[%sm%s%s\033[0m", [
            $this->colors[$color],
            is_null($background) ? '' : sprintf("\033[%sm", $this->backgrounds[$background]),
            $text,
        ]);
    }

    /**
     * Vide l'écran
     *
     * @return void
     */
    public function clear(): void
    {
        fwrite(STDOUT, chr(27) . '[H' . chr(27) . '[2J');
    }
}
