<?php

namespace Pexess\Exceptions;

use Pexess\Helpers\StatusCodes;

class MethodNotAllowedException extends HttpException
{
    public function __construct()
    {
        parent::__construct("Method Not Allowed", StatusCodes::METHOD_NOT_ALLOWED);
    }
}