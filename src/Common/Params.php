<?php

namespace Pexess\Common;

use Pexess\Pexess;

class Params
{
    public function __construct()
    {
        $params = Pexess::$routeParams;

        foreach ($params as $param => $value) {
            $this->$param = $value;
        }
    }
}