<?php

namespace Pexess\Common;

class Body
{
    public function __construct()
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $data= filter_var_array($body, FILTER_SANITIZE_SPECIAL_CHARS);
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}