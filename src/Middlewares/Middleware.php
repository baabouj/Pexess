<?php

namespace Pexess\Middlewares;

use Closure;
use Pexess\Http\Request;
use Pexess\Http\Response;

interface Middleware
{
    public function handler(Request $req, Response $res, Closure $next);
}