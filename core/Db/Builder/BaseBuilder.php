<?php

namespace Core\Db\Builder;

abstract class BaseBuilder
{
    /** @var array $select */
    protected $select = ['*'];
    /** @var array $from */
    protected $from = false;
    /** @var array $where */
    protected $where = [];
    /** @var int|bool $limit */
    protected $limit = false;
    /** @var int|bool $offset */
    protected $offset = false;

    public function select($columns = '*'): self
    {
        $this->select = is_string($columns) ? [$columns] : $columns;

        return $this;
    }

    public function where(string $column, $value = null): self
    {
        $this->where[] = [$column, '=', $value, 'AND'];

        return $this;
    }

    public function orWhere(string $column, $value = null): self
    {
        $this->where[] = [$column, '=', $value, 'OR'];

        return $this;
    }

    public function whereNot(string $column, $value = null): self
    {
        $this->where[] = [$column, '!=', $value, 'AND'];

        return $this;
    }

    public function orWhereNot(string $column, $value = null): self
    {
        $this->where[] = [$column, '!=', $value, 'OR'];

        return $this;
    }

    public function take(int $length = 1): self
    {
        $this->limit = $length;

        return $this;
    }

    public function skip(int $length = 1): self
    {
        $this->offset = $length;

        return $this;
    }

    public function table(string $table): self
    {
        $this->from = $table;

        return $this;
    }

    public function get(): string
    {
        $query = $this->compileSelect();
        $this->reset();

        return $query;
    }

    protected function compileSelect(): string
    {
        $parts = [];
        $parts[] = $this->compileSelectColumns();

        if (!empty($this->from)) {
            $parts[] = $this->compileFrom();
        }

        if (!empty($this->where)) {
            $parts[] = $this->compileWhere($this->where);
        }

        if ($this->limit || $this->offset) {
            $parts[] = $this->compileLimitOffset();
        }

        return implode(' ', $parts);
    }

    protected function compileSelectColumns(): string
    {
        if (count($this->select) == 1 && $this->select[0] == '*') {
            $columns = '*';
        } else {
            $columns = array_map(function ($column) {
                return sprintf('`%s`', $column);
            }, $this->select);

            $columns = implode(', ', $columns);
        }

        return sprintf('SELECT %s', $columns);
    }

    protected function compileFrom(): string
    {
        return sprintf('FROM `%s`', $this->from);
    }

    protected function compileWhere(array $wheres): ?string
    {
        $parts = [];
        $count = 0;

        foreach ($wheres as $where) {
            list($column, $operator, $value, $type) = $where;

            $parts[] = vsprintf(
                '%s`%s` %s %s',
                [
                    $count == 0 ? '' : sprintf('%s ', $type),
                    $column,
                    $operator,
                    $value
                ]
            );

            $count++;
        }

        return sprintf('WHERE %s', implode(' ', $parts));
    }

    protected function compileLimitOffset(): ?string
    {
        $parts = [];

        if ($this->limit !== false) {
            $parts[] = sprintf('LIMIT %d', $this->limit);
        }

        // Offset
        if ($this->limit !== false) {
            $parts[] = sprintf('OFFSET %d', $this->offset);
        }

        return implode(' ', $parts);
    }

    protected function reset(): self
    {
        $this->select = ['*'];
        $this->where = [];
        $this->from = false;
        $this->limit = false;
        $this->offset = false;

        return $this;
    }
}
