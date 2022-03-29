<?php

namespace Pexess\Database;

class Database
{
    private \PDO $pdo;

    private \PDOStatement|false $statement;

    private static ?Database $instance = null;

    private function __construct()
    {
        $this->pdo = new \PDO($_ENV["DB_DSN"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public static function instance(): Database
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public static function from($table): QueryBuilder
    {
        return new QueryBuilder($table);
    }

    public function query($sql)
    {
        $this->statement = $this->pdo->prepare($sql);
    }

    public function bind($parameter, $value, $type = null)
    {
        $type = match (is_null($type)) {
            is_int($value) => \PDO::PARAM_INT,
            is_bool($value) => \PDO::PARAM_BOOL,
            is_null($value) => \PDO::PARAM_NULL,
            default => \PDO::PARAM_STR,
        };
        $this->statement->bindValue($parameter, $value, $type);
    }

    public function execute(): bool
    {
        return $this->statement->execute();
    }

    public function resultSet(): bool|array
    {
        $this->execute();
        return $this->statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function single()
    {
        $this->execute();
        return $this->statement->fetch(\PDO::FETCH_ASSOC);
    }

    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }
}