<?php

namespace Pexess\Http;

use Pexess\Pexess;

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
        $body = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        $json = json_decode(file_get_contents('php://input'), true);
        if ($json ?? false) {
            $body = filter_var_array($json, FILTER_SANITIZE_SPECIAL_CHARS);
        }
        return $body;
    }

    public function query(): array
    {
        return filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    public function params(): array
    {
        return Pexess::$routeParams ?? [];
    }

    public function headers(): bool|array
    {
        return array_change_key_case(getallheaders(), CASE_LOWER);
    }
}