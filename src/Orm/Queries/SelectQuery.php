<?php

namespace Pexess\Orm\Queries;

class SelectQuery extends Query
{
    private string|array $select = "*";
    private null|array $where = null;
    private string|array|null $orderBy = null;
    private int|null $take = null;
    private int|null $skip = null;

    public function select($select): static
    {
        $this->select = $select;
        return $this;
    }

    public function from(string $from): static
    {
        $this->table = $from;
        return $this;
    }

    public function where(array $where): static
    {
        $this->where = $where;
        return $this;
    }

    public function orderBy(string $by, string $order = 'asc'): static
    {
        $this->orderBy = [$by, strtoupper($order)];
        return $this;
    }

    public function take(int $take): static
    {
        $this->take = $take;
        return $this;
    }

    public function skip(int $skip): static
    {
        $this->skip = $skip;
        return $this;
    }

    public function getQuery(): string
    {
        is_array($this->select) && $this->select = implode(", ", $this->select);
        $query = 'SELECT ' . $this->select . ' FROM ' . $this->table;

        if ($this->where) {
            $query .= $this->generateWhereQuery($this->where);
        }

        if ($this->orderBy) {
            $query .= $this->generateOrderByQuery($this->orderBy);
        }

        if ($this->take) {
            $query .= $this->generateLimitQuery($this->take);
        }

        if ($this->skip) {
            $query .= $this->generateOffsetQuery($this->skip);
        }

        return $query;
    }

    private function generateOrderByQuery(array $orderBy): string
    {
        [$by, $order] = $orderBy;

        return " ORDER BY $by $order";
    }

    private function generateLimitQuery(int $take): string
    {
//        $this->bind($take);
        return " LIMIT $take";
    }

    private function generateOffsetQuery(int $skip): string
    {
        $this->bind($skip);
        return " OFFSET ?";
    }

}