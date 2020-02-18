<?php

namespace Core\Db;

use Core\Db\Exceptions\DatabaseException;
use Core\Foundation\Config;
use PDO;

class DB
{
    /** @var Config $config */
    protected $config;
    /** @var PDO|null $pdo */
    protected $pdo;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Retourne l'instance de connexion à la base de données
     *
     * @return PDO
     * @throws DatabaseException
     */
    public function get(): PDO
    {
        if (empty($this->pdo)) {
            $this->create();
        }

        return $this->pdo;
    }

    /**
     * Execute une requête de lecture d'une row
     *
     * @param string $query
     * @param array  $params
     * @return array|null
     * @throws DatabaseException
     */
    public function modelQuery(string $query, array $params)
    {
        try {
            $statement = db()->get()->prepare($query);
            $statement->execute(array_values($params));
        } catch (\Exception $e) {
            throw new DatabaseException(
                sprintf(
                    "Erreur dans la requête %s avec les paramètres %s",
                    $statement->queryString,
                    implode(', ', $params)
                )
            );
        }

        return $statement->fetch();
    }

    /**
     * Execute une requête d'écriture d'une ou plusieurs rows
     *
     * @param string $query
     * @param array  $params
     * @return bool
     * @throws DatabaseException
     */
    public function modelExec(string $query, array $params): bool
    {
        try {
            $statement = db()->get()->prepare($query);
            $statement->execute(array_values($params));
        } catch (\Exception $e) {
            throw new DatabaseException(
                sprintf(
                    "Erreur dans la requête %s avec les paramètres %s",
                    $statement->queryString,
                    implode(', ', $params)
                )
            );
        }

        return $statement->rowCount() > 0;
    }

    /**
     * Ajoute des quotes au nom fourni
     *
     * @param string $name
     * @return string
     */
    public function quote(string $name): string
    {
        return sprintf('`%s`', $name);
    }

    /**
     * Quote un tableau de colonne puis les implode
     *
     * @param array $columns
     * @return string
     */
    public function quoteColumns(array $columns): string
    {
        $columns = array_map(function ($name) {
            return sprintf('`%s`', $name);
        }, $columns);

        return implode(', ', $columns);
    }

    /**
     * Crée l'instance de connexion à la base de données
     *
     * @return self
     * @throws DatabaseException
     */
    protected function create(): self
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $dsn = vsprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8',
            [
                $this->config->get('db_host'),
                $this->config->get('db_port'),
                $this->config->get('db_name'),
            ]
        );

        try {
            $this->pdo = new PDO($dsn, $this->config->get('db_user'), $this->config->get('db_pass'), $options);
        } catch (\Exception $e) {
            throw new DatabaseException(
                vsprintf(
                    "Impossible de se connecter à la base %s (%s:%s)",
                    [
                        $this->config->get('db_name'),
                        $this->config->get('db_host'),
                        $this->config->get('db_port'),
                    ]
                )
            );
        }

        return $this;
    }
}
