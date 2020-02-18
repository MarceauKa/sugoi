<?php

namespace App\Core;

use Core\Db\Exceptions\DatabaseException;
use Core\Db\Exceptions\ModelException;

abstract class Model
{
    /** @var string $table */
    protected $table;
    /** @var array $attributes */
    protected $attributes = [];
    /** @var array $original */
    protected $original = [];
    /** @var array $columns */
    protected $columns = [];
    /** @var string $primaryKey */
    protected $primaryKey = 'id';
    /** @var bool $exists */
    protected $exists = false;

    public function __construct(array $attributes = [], bool $exists = false)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        $this->original = $this->attributes;
        $this->exists = $exists;
    }

    /**
     * Crée un nouveau model avec les attributs fournis
     *
     * @param array $attributes
     * @return self
     */
    protected function create(array $attributes = []): self
    {
        return new static($attributes, false);
    }

    /**
     * Enregistre le model en base de données
     *
     * @return bool
     * @throws DatabaseException
     * @throws ModelException
     */
    public function save(): bool
    {
        // Aucune modif et le model existe déjà, on enregistre pas
        if (false === $this->changed() && $this->exists()) {
            return false;
        }

        // Le model n'existe pas en base, on insère
        if (false === $this->exists()) {
            $this->setTimestamp('created_at');

            $query = vsprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                [
                    db()->quote($this->getTable()),
                    db()->quoteColumns(array_keys($this->attributes)),
                    substr(str_repeat('?,', count($this->attributes)), 0, -1),
                ]
            );

            $created = db()->modelExec($query, $this->attributes);

            if ($created) {
                // On récupère l'ID attribué à l'insertion
                $this->attributes[$this->getPrimaryKey()] = db()->get()->lastInsertId($this->getPrimaryKey());
                $this->original = $this->attributes;
                $this->exists = true;
            }

            return $created;
        }

        // Le model existe déjà en base, on update
        if (true === $this->exists()) {
            // Mise à jour de la colonne "updated_at"
            $this->setTimestamp('updated_at');

            // Récupération des différences dans les données
            $changed = $this->diff();
            $updateParams = [];

            foreach ($changed as $key => $value) {
                $updateParams[] = sprintf('`%s` = ?', $key);
            }

            $query = vsprintf(
                'UPDATE %s SET %s WHERE %s = %d LIMIT 1',
                [
                    db()->quote($this->getTable()),
                    implode(', ', $updateParams),
                    db()->quote($this->getPrimaryKey()),
                    $this->attributes[$this->getPrimaryKey()],
                ]
            );

            $updated = db()->modelExec($query, array_values($changed));

            if ($updated) {
                $this->original = $this->attributes;
            }

            return $updated;
        }

        throw new ModelException(sprintf("Impossible de sauvegarder le model %s", get_class($this)));
    }

    /**
     * Récupère un model depuis son ID
     *
     * @param int|mixed $id
     * @return self
     * @throws DatabaseException
     */
    protected function find($id): ?self
    {
        $query = vsprintf(
            'SELECT * FROM %s WHERE %s = ? LIMIT 1',
            [
                db()->quote($this->getTable()),
                $this->getPrimaryKey()
            ]
        );

        $params = [$id];

        $result = db()->modelQuery($query, $params);

        if ($result) {
            return new static($result, true);
        }

        return null;
    }

    /**
     * Le modèle existe en base ?
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->exists;
    }

    /**
     * Le modèle à changé depuis sa récupération ?
     *
     * @return bool
     */
    public function changed(): bool
    {
        if (empty($this->original) && !empty($this->attributes)) {
            return true;
        }

        return count($this->diff()) > 0;
    }

    /**
     * Récupère un tableau des attributs qui ont été modifiés
     *
     * @return array
     */
    public function diff(): array
    {
        $changed = [];

        foreach ($this->attributes as $key => $value) {
            if (false === array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $changed[$key] = $value;
            }
        }

        return $changed;
    }

    /**
     * Récupère la table associée au model
     *
     * @return string
     * @throws ModelException
     */
    public function getTable(): string
    {
        if (empty($this->table)) {
            throw new ModelException("Pas de table définie pour %s", get_class($this));
        }

        return $this->table;
    }

    /**
     * Récupère les colonnes de la table
     *
     * @return array
     * @throws ModelException
     */
    public function getColumns(): array
    {
        if (empty($this->columns)) {
            throw new ModelException("Pas de colonnes définies pour %s", get_class($this));
        }

        return $this->columns;
    }

    /**
     * Récupère le nom de la clé primaire
     *
     * @return string
     * @throws ModelException
     */
    public function getPrimaryKey(): string
    {
        if (empty($this->primaryKey)) {
            throw new ModelException("Pas de clé primaire définie pour %s", get_class($this));
        }

        return $this->primaryKey;
    }

    /**
     * Met à jour une colonne de timestamp
     *
     * @param string $column
     * @return self
     * @throws \Exception
     */
    public function setTimestamp(string $column): self
    {
        if (in_array($column, $this->columns)) {
            $this->attributes[$column] = (new \DateTime())->format('Y-m-d H:i:s');
        }

        return $this;
    }

    /**
     * Récupère un attribut s'il existe pour ce model
     *
     * @param $name
     * @return mixed
     * @throws ModelException
     */
    public function __get($name)
    {
        // L'attribut est défini
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        // L'attribut n'est pas défini mais est déclaré dans les colonnes
        if (in_array($name, $this->columns)) {
            return null;
        }

        throw new ModelException(sprintf("L'attribut %s n'existe pas sur le model %s", $name, get_class($this)));
    }

    /**
     * Définit la valeur d'un attribut pour le model
     * et empêche de muter la clé primaire
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @throws ModelException
     */
    public function __set($name, $value)
    {
        if ($name === $this->getPrimaryKey()) {
            throw new ModelException(sprintf("La clé primaire %s ne peut être mutée sur %s", $this->getPrimaryKey(), get_class($this)));
        }

        // L'attribut correspond à une colonne du model
        if (in_array($name, $this->columns)) {
            return $this->attributes[$name] = $value;
        }

        throw new ModelException(sprintf("L'attribut %s n'existe pas sur le model %s", $name, get_class($this)));
    }

    /**
     * Ajoute une interface statique aux méthodes du model
     *
     * @param string $name
     * @param array|mixed $arguments
     * @return mixed
     * @throws ModelException
     */
    public static function __callStatic($name, $arguments)
    {
        $instance = new static();

        if (method_exists($instance, $name)) {
            return $instance->$name(...$arguments);
        }

        throw new ModelException(sprintf("La méthode %s n'existe pas sur le model %s", $name, get_class($instance)));
    }
}
