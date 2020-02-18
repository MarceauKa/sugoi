<?php

namespace Core\Foundation;

use Traversable;

class Collection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /** @var array $items */
    public $items;

    public function __construct($items = [])
    {
        if (is_array($items)) {
            $this->items = $items;
        } else if ($items instanceof Collection) {
            $this->items = $items->all();
        } else {
            $this->items = (array)$items;
        }
    }

    /**
     * Crée une instance de manière statique
     *
     * @param array|self $items
     * @return static
     */
    public static function make($items): self
    {
        return new static($items);
    }

    /**
     * Retourne toutes les valeurs (avec leur clé)
     *
     * @return array
     */
    public function all(): array
    {
        return (array)$this->items;
    }

    /**
     * Retourne les clés des éléments
     *
     * @return self
     */
    public function keys(): self
    {
        return new self(array_keys($this->items));
    }

    /**
     * Retourne les valeurs des éléments (perd les clés)
     *
     * @return self
     */
    public function values(): self
    {
        return new self(array_values($this->items));
    }

    /**
     * Mélange les éléments
     *
     * @return self
     */
    public function randomize(): self
    {
        shuffle($this->items);

        return new self($this->items);
    }

    /**
     * Compte les éléments
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Retourne un élément par sa clé
     *
     * @param string|int $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        foreach ($this->items as $index => $value) {
            if ($index === $key) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Retourne le premier élément
     *
     * @param mixed|null $default
     * @return mixed|null
     */
    public function first($default = null)
    {
        reset($this->items);

        return current($this->items) ?? $default;
    }

    /**
     * Retourne le dernier élément
     *
     * @param mixed|null $default
     * @return mixed|null
     */
    public function last($default = null)
    {
        return end($this->items) ?? $default;
    }

    /**
     * Vérifie que la collection est vide
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Vérifie que la collection n'est pas vide
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return false === $this->isEmpty();
    }

    /**
     * Passe une fonction sur l'ensemble des éléments du tableau
     *
     * @param callable $callback
     * @return self
     */
    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->items));
    }

    /**
     * Filtre les éléments selon la fonction fournie
     *
     * @param callable $callback
     * @return self
     */
    public function filter(callable $callback): self
    {
        return new self(array_filter($this->all(), $callback, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Retourne les éléments correspond à la condition
     *
     * @param string|int $key
     * @param string|mixed $operator
     * @param mixed|null $value
     * @return self
     */
    public function where($key, $operator, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        return $this->filter(function ($item) use ($key, $operator, $value) {
             switch ($operator) {
                 case '!=':
                 case '<>':
                     return $item[$key] != $value;
                     break;
                 case '!==':
                     return $item[$key] !== $value;
                     break;
                 case '===':
                     return $item[$key] === $value;
                     break;
                 case '>':
                     return $item[$key] > $value;
                     break;
                 case '>=':
                     return $item[$key] >= $value;
                     break;
                 case '<':
                     return $item[$key] < $value;
                     break;
                 case '<=':
                     return $item[$key] <= $value;
                     break;
                 case '=':
                 case '==':
                 default:
                     return $item[$key] == $value;
                     break;
             }
        });
    }

    /**
     * Retourne le premier élément qui répond à la condition
     *
     * @param string|int $key
     * @param string|mixed $operator
     * @param mixed|null $value
     * @return self
     */
    public function firstWhere($key, $operator, $value)
    {
        return $this->where(...func_get_args())->first();
    }

    /**
     * Transforme les éléments en suivant la fonction fournie
     *
     * @param callable $callback
     * @return self
     */
    public function transform(callable $callback): self
    {
        $items = [];

        foreach ($this->items as $key => $value) {
            $items[$key] = $callback($value, $key);
        }

        return new self($items);
    }

    /**
     * Retourne seulement les clés demandées pour chaque élément
     *
     * @param array $keys
     * @return self
     */
    public function only($keys): self
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $items = [];

        foreach ($this->items as $item) {
            $newItem = [];

            foreach ($item as $key => $value) {
                if (in_array($key, $keys)) {
                    $newItem[$key] = $value;
                }
            }

            $items[] = $newItem;
        }

        return new self($items);
    }

    /**
     * Inverse les clés et les valeurs
     *
     * @return self
     */
    public function flip(): self
    {
        return new self(array_flip($this->items));
    }

    /**
     * Réduit un tableau selon les clés fournies
     *
     * @param string $valueKey
     * @param string|null $key
     * @return self
     */
    public function pluck($valueKey, $key = null): self
    {
        $items = [];

        foreach ($this->items as $index => $value) {
            $newValue = $value[$valueKey];

            if (is_null($key)) {
                $items[] = $newValue;
            } else {
                $items[$value[$key]] = $newValue;
            }
        }

        return new self($items);
    }

    /**
     * Réduit une valeur des éléments selon le séparateur fourni
     *
     * @param string $key
     * @param string $glue
     * @return string
     */
    public function implode(string $key, string $glue = ','): string
    {
        return implode($glue, $this->pluck($key)->all());
    }

    /**
     * Retourne un certain nombre d'élément
     *
     * @param int $limit
     * @return self
     */
    public function take(int $limit = 1): self
    {
        return $this->slice(0, $limit);
    }

    /**
     * Retourne un certain nombre d'élément selon un point de départ fourni
     *
     * @param int $offset
     * @param int $length
     * @return self
     */
    public function slice(int $offset, int $length = 1): self
    {
        return new static(array_slice($this->items, $offset, $length, true));
    }

    /**
     * Affiche tous les éléments et arrête l'éxécution de l'application
     */
    public function dd(): void
    {
        dd($this->items);
    }

    /**
     * Retourne la collection en JSON
     *
     * @return false|string
     */
    public function toJson()
    {
        return json_encode($this->all());
    }

    /**
     * @see \ArrayAccess
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * @see \ArrayAccess
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->items[$offset] : null;
    }

    /**
     * @see \ArrayAccess
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    /**
     * @see \ArrayAccess
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->items[$offset]);
        }
    }

    /**
     * @see \ArrayIterator
     * @return \ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}
