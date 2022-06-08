<?php

namespace Pexess\Orm;

class Connector
{
    public static function connect(): \PDO
    {
        $connection = new \PDO($_ENV["DB_DSN"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);
        $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $connection;
    }
}