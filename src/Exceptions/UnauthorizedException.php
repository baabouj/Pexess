<?php

namespace Pexess\Exceptions;

use Pexess\Helpers\StatusCodes;

class UnauthorizedException extends HttpException
{
    public function __construct()
    {
        parent::__construct("Unauthorized", StatusCodes::UNAUTHORIZED);
    }
}