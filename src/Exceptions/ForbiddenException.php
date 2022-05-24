<?php

namespace Pexess\Exceptions;

use Pexess\Helpers\StatusCodes;

class ForbiddenException extends HttpException
{
    public function __construct()
    {
        parent::__construct("Forbidden", StatusCodes::FORBIDDEN);
    }
}