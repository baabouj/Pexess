<?php

namespace Pexess\Http;

class Request
{
    public function url(): string
    {
        $path = $_SERVER['REQUEST_URI'];
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        return rtrim($path, '/') ?: '/';
    }

    public function method(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function body(): array
    {
        $body = file_get_contents("php://input");
        return json_decode($body, true) ?? [];
    }

    public function query(): array
    {
        return $_GET;
    }

    public function headers(): bool|array
    {
        return array_change_key_case(getallheaders(), CASE_LOWER);
    }
}