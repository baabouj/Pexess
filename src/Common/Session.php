<?php

namespace Pexess\Common;

class Session
{
    public function __construct()
    {
        session_start();
    }

    public function __destruct()
    {
        session_destroy();
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key)
    {
        return $_SESSION[$key];
    }

    public function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function all(): array
    {
        return $_SESSION;
    }

    public function reset(): void
    {
        unset($_SESSION);
    }
}