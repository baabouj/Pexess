<?php

namespace Pexess\Middlewares;

use Closure;
use Pexess\Http\Request;
use Pexess\Http\Response;

class Cors
{
    public static function set(array $cors): Closure
    {
        return function (Request $req, Response $res, Closure $next) use ($cors) {

            isset($cors['origin']) && self::origin($cors['origin']);

            isset($cors['headers']) && self::headers($cors['headers']);

            isset($cors['methods']) && self::methods($cors['methods']);

            isset($cors['maxAge']) && self::maxAge($cors['maxAge']);

            isset($cors['exposeHeaders']) && self::exposeHeaders($cors['exposeHeaders']);

            isset($cors['credentials']) && self::credentials($cors['credentials']);

            $next();
        };
    }

    public static function origin($origin): void
    {
        if (is_bool($origin)) {
            $origin ?
                header("Access-Control-Allow-Origin: *") :
                header_remove("Access-Control-Allow-Origin");
            return;
        }

        if (is_string($origin) && func_num_args() > 1) {
            $origin = func_get_args();
        }

        if (is_array($origin)) {
            $http_origin = $_SERVER["HTTP_ORIGIN"];
            $origin = in_array($http_origin, $origin) ? $http_origin : $origin[0];
        }

        header("Access-Control-Allow-Origin: $origin");
    }

    public static function headers($headers): void
    {
        if (is_bool($headers)) {
            $headers ?
                header("Access-Control-Allow-Headers: *") :
                header_remove("Access-Control-Allow-Headers");
            return;
        }

        if (is_string($headers) && func_num_args() > 1) {
            $headers = func_get_args();
        }

        if (is_array($headers)) $headers = implode(", ", $headers);

        header("Access-Control-Allow-Headers: " . $headers);
    }

    public static function methods($methods): void
    {
        if (is_string($methods) && func_num_args() > 1) {
            $methods = func_get_args();
        }

        if (is_array($methods)) $methods = implode(", ", $methods);

        header("Access-Control-Allow-Methods: " . $methods);
    }

    public static function maxAge($maxAge): void
    {
        header("Access-Control-Allow-MaxAge: $maxAge");
    }

    public static function exposeHeaders($exposeHeaders): void
    {
        if (is_bool($exposeHeaders)) {
            $exposeHeaders ?
                header("Access-Control-Expose-Headers: *") :
                header_remove("Access-Control-Expose-Headers");
            return;
        }

        if (is_string($exposeHeaders) && func_num_args() > 1) {
            $exposeHeaders = func_get_args();
        }

        if (is_array($exposeHeaders)) $exposeHeaders = implode(", ", $exposeHeaders);

        header("Access-Control-Expose-Headers: " . $exposeHeaders);
    }

    public static function credentials(bool $allowCredentials = true): void
    {
        $allowCredentials ?
            header("Access-Control-Allow-Credentials: true") :
            header_remove("Access-Control-Allow-Credentials");
    }
}