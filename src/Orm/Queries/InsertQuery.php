<?php

namespace Pexess\Orm\Queries;

class InsertQuery extends Query
{
    private array $columns;
    private array $values;

    public function into(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    public function insert(array $inserts): static
    {
        $this->columns = array_keys($inserts);
        $this->values = array_values($inserts);

        return $this;
    }

    public function getQuery(): string
    {
        $values = array_map(function ($value) {
            $this->bind($value);
            return '?';
        }, $this->values);

        return 'INSERT INTO ' . $this->table . ' (' . implode(', ', $this->columns) . ') VALUES (' . implode(', ', $values) . ')';
    }

}