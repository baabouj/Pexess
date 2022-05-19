<?php

namespace Pexess\Common;

class Query
{
    public function __construct()
    {
        $query = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];

        foreach ($query as $key => $value) {
            $this->$key = $value;
        }
    }
}