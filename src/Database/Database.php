<?php

namespace Pexess\Database;

use PDO;
use PDOStatement;
use Pexess\Orm\Connector;
use Pexess\Orm\QueryBuilder;

class Database
{
    private PDO $pdo;

    private PDOStatement|false $statement;

    private static self $instance;

    private function __construct()
    {
        $this->pdo = Connector::connect();
    }

    public static function instance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function from($table): QueryBuilder
    {
        return new QueryBuilder($table);
    }

    public function query(string $sql, array $bindings): static
    {
        $this->statement = $this->pdo->prepare($sql);
        foreach ($bindings as $index => $binding) {
            $this->bind($index + 1, $binding);
        }
        $this->execute();

        return $this;
    }

    public function bind($parameter, $value, $type = null)
    {
        $type = match (is_null($type)) {
            is_int($value) => PDO::PARAM_INT,
            is_bool($value) => PDO::PARAM_BOOL,
            is_null($value) => PDO::PARAM_NULL,
            default => PDO::PARAM_STR,
        };
        $this->statement->bindValue($parameter, $value, $type);
    }

    public function execute(): bool
    {
        return $this->statement->execute();
    }

    public function all(): bool|array
    {
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): bool|array
    {
        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }

    public function lastInsertedId(): bool|string
    {
        return $this->pdo->lastInsertId();
    }

}