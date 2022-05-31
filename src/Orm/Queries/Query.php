<?php

namespace Pexess\Orm\Queries;

abstract class Query
{
    protected string $table;

    protected array $bindings = [];

    abstract public function getQuery(): string;

    public function getBindings(): array
    {
        return $this->bindings;
    }

    protected function bind(mixed $value)
    {
        $this->bindings[] = $value;
    }

    protected function generateWhereQuery($where): string
    {
        $query = '';

        foreach ($where as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $operator => $v) {
                    switch ($operator) {
                        case 'gt':
                            $query .= $key . ' > ? AND ';
                            $this->bind($v);
                            break;

                        case 'gte':
                            $query .= $key . ' >= ? AND ';
                            $this->bind($v);
                            break;

                        case "lt":
                            $query .= $key . ' < ? AND ';
                            $this->bind($v);
                            break;

                        case "lte":
                            $query .= $key . ' <= ? AND ';
                            $this->bind($v);
                            break;

                        case "eq":
                            $query .= $key . ' = ? AND ';
                            $this->bind($v);
                            break;

                        case "contains":
                            $query .= $key . " LIKE ? AND ";
                            $this->bind('%' . $v . '%');
                            break;

                        case "startsWith":
                            $query .= $key . ' LIKE ? AND ';
                            $this->bind($v . '%');
                            break;

                        case "endsWith":
                            $query .= $key . ' LIKE ? AND ';
                            $this->bind('%' . $v);
                            break;

                        default:
                            break;

                    }
                }
            } else {
                $query .= $key . ' = ? AND ';
                $this->bind($value);
            }
        }


        return ' WHERE ' . substr($query, 0, -5);
    }

}