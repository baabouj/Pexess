<?php

namespace Pexess\Models;

use Pexess\Database\QueryBuilder;

abstract class Model extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct($this->table());
    }

    abstract protected function table(): string;
}