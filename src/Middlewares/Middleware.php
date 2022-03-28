<?php

namespace Pexess\Middlewares;

use Pexess\Http\Request;
use Pexess\Http\Response;

interface Middleware
{
    public function handler(Request $req,Response $res, $next);
}