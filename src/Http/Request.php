<?php

namespace Pexess\Http;

use Pexess\Pexess;
use Pexess\Validator\Validator;

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
        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        return filter_var_array($body, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    public function query(): array
    {
        return filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
    }

    public function validate(array $rules): bool|array
    {
        return Validator::validate(array_merge($this->query(), $this->body()), $rules);
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