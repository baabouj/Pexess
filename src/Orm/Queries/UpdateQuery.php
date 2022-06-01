<?php

namespace Pexess\Orm\Queries;

class UpdateQuery extends Query
{
    private array|string $columns;
    private array $where;

    public function from(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    public function update(string|array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function to($values): static
    {
        if (is_array($values)) {
            foreach ($values as $value) {
                $this->bind($value);
            }
        } else {
            $this->bind($values);
        }

        return $this;
    }

    public function where($where): static
    {
        $this->where = $where;

        return $this;
    }

    public function getQuery(): string
    {
        $updates = array_map(fn($col) => $col . ' = ?', $this->columns);
        return 'UPDATE ' . $this->table . ' SET ' . implode(', ', $updates) . $this->generateWhereQuery($this->where);
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

}