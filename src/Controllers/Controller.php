<?php

namespace Pexess\Controllers;

use Pexess\Validator\Validator;

class Controller
{
    public function validate(array $body, array $data): array|bool
    {
        return Validator::validate($body,$data);
    }
}