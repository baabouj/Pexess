<?php

namespace Pexess\exceptions;

use Pexess\Helpers\StatusCodes;

class MethodNotAllowedException extends \Exception
{
    protected $code = StatusCodes::METHOD_NOT_ALLOWED;
    protected $message = "Method Not Allowed";
}