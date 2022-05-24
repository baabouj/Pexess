<?php

namespace Pexess\Exceptions;

use Pexess\Helpers\StatusCodes;

class InternalServerErrorException extends HttpException
{
    public function __construct()
    {
        parent::__construct("Internal server error", StatusCodes::INTERNAL_SERVER_ERROR);
    }
}