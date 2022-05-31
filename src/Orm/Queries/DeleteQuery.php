<?php

namespace Pexess\Orm\Queries;

class DeleteQuery extends Query
{

    private array $where;

    public function from($table): static
    {
        $this->table = $table;

        return $this;
    }

    public function where($where): static
    {
        $this->where = $where;

        return $this;
    }

    public function getQuery(): string
    {
        return 'DELETE FROM ' . $this->table . $this->generateWhereQuery($this->where);
    }
}