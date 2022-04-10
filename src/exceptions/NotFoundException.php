<?php

namespace Pexess\exceptions;

use Pexess\Helpers\StatusCodes;

class NotFoundException extends \Exception
{
    protected $code = StatusCodes::NOT_FOUND;
    protected $message = "Not Found";
}