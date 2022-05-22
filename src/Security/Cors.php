<?php

namespace Pexess\Security;

class Cors
{

    public static function origin($origin)
    {
        if (is_string($origin) && func_num_args() > 1) {
            $origin = func_get_args();
        }

        if (is_array($origin)) {
            $http_origin = $_SERVER["HTTP_ORIGIN"];
            $origin = in_array($http_origin, $origin) ? $http_origin : $origin[0];
        }

        if (is_bool($origin)) {
            $origin = $origin ? "*" : "";
        }

        header("Access-Control-Allow-Origin: $origin");

        return self::class;
    }

    public static function headers($headers)
    {
        if (is_bool($headers)) {
            $headers = $headers ? "*" : "";
        }

        if (is_string($headers) && func_num_args() > 1) {
            $headers = func_get_args();
        }

        if (is_array($headers)) $headers = implode(", ", $headers);

        header("Access-Control-Allow-Headers: " . $headers);

        return self::class;
    }

    public static function methods($methods)
    {

        if (is_string($methods) && func_num_args() > 1) {
            $methods = func_get_args();
        }

        if (is_array($methods)) $methods = implode(", ", $methods);

        header("Access-Control-Allow-Methods: " . $methods);

        return self::class;
    }

    public static function maxAge($maxAge)
    {
        header("Access-Control-Allow-MaxAge: $maxAge");

        return self::class;
    }
}