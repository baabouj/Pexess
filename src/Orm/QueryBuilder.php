<?php

namespace Pexess\Orm;

use Pexess\Database\Database;
use Pexess\ORM\Queries\DeleteQuery;
use Pexess\ORM\Queries\InsertQuery;
use Pexess\ORM\Queries\SelectQuery;
use Pexess\ORM\Queries\UpdateQuery;

class QueryBuilder
{
    protected string $table;
    private Database $db;

    public function __construct($table)
    {
        $this->db = Database::instance();
        $this->table = $table;
    }

    public function create(array $options): bool|object
    {
        $db = Database::instance();
        $builder = new InsertQuery();

        $builder
            ->into($this->table)
            ->insert($options["data"]);

        [$query, $bindings] = [$builder->getQuery(), $builder->getBindings()];

        $db->query($query, $bindings);

        return $this->findUnique([
            'where' => [
                'id' => $this->db->lastInsertedId()
            ],
            'select' => $options["select"] ?? '*'
        ]);
    }

    public function update(array $options): bool|object
    {
        $builder = new UpdateQuery();

        $builder
            ->from($this->table)
            ->update(array_keys($options['data']))
            ->to(array_values($options['data']))
            ->where($options['where']);

        $this->db->query($builder->getQuery(), $builder->getBindings());

        return $this->findUnique([
            'where' => $options['where']
        ]);
    }

    public function delete(array $options): bool|object
    {
        $builder = new DeleteQuery();

        $builder->from($this->table)->where($options['where']);

        $record = $this->findUnique([
            'where' => $options['where'],
            'select' => $options['select'] ?? '*'
        ]);

        $this->db->query($builder->getQuery(), $builder->getBindings());

        return $record;
    }

    public function findUnique(array $options): bool|object
    {
        $builder = new SelectQuery();
        $builder->from($this->table);

        if (isset($options['select'])) {
            $builder->select($options['select']);
        }

        if (isset($options['where'])) {
            $builder->where($options['where']);
        }

        [$query, $bindings] = [$builder->getQuery(), $builder->getBindings()];

        $this->db->query($query, $bindings);

        $class = is_subclass_of(get_called_class(),Entity::class) ? get_called_class() : 'stdClass';

        return $this->db->first($class);
    }

    public function findMany(array $options = []): bool|array
    {
        $builder = new SelectQuery();
        $builder->from($this->table);

        if (isset($options['select'])) {
            $builder->select($options['select']);
        }

        if (isset($options['where'])) {
            $builder->where($options['where']);
        }

        if (isset($options['orderBy'])) {
            is_array($options['orderBy'])
                ? [$by, $order] = [...$options['orderBy'], 'asc']
                : [$by, $order] = [$options['orderBy'], 'asc'];
            $builder->orderBy($by, $order);
        }

        if (isset($options['take'])) {
            $builder->take($options['take']);
        }

        if (isset($options['skip'])) {
            $builder->skip($options['skip']);
        }

        [$query, $bindings] = [$builder->getQuery(), $builder->getBindings()];

        $this->db->query($query, $bindings);

        $class = is_subclass_of(get_called_class(),Entity::class) ? get_called_class() : 'stdClass';

        return $this->db->all($class);
    }

    public function count(array $options = []): int|bool
    {
        $builder = new SelectQuery();
        $builder->from($this->table);

        $builder->select('COUNT(*) AS count');

        if (isset($options['where'])) {
            $builder->where($options['where']);
        }

        [$query, $bindings] = [$builder->getQuery(), $builder->getBindings()];

        return $this->db->query($query, $bindings)->first()->count ?? false;
    }

    public function groupBy(array $options)
    {
        // TODO: Implement groupBy functionality
    }
}
