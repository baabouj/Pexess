<?php

namespace Pexess\Exceptions;

use Pexess\Helpers\StatusCodes;

class BadRequestException extends HttpException
{
    public function __construct()
    {
        parent::__construct("Bad Request", StatusCodes::BAD_REQUEST);
    }
}