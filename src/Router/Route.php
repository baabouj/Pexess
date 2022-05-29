<?php

namespace Pexess\Router;

class Route
{

    public function __construct(
        private string       $route,
        private string|array $method = "*"
    )
    {
//        echo "<h1>$route</h1>";
    }

    public function get(callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, 'get', $handler);
        return $this;
    }

    public function post(callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, 'post', $handler);
        return $this;
    }

    public function put(callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, 'put', $handler);
        return $this;
    }

    public function patch(callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, 'patch', $handler);
        return $this;
    }

    public function delete(callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, 'delete', $handler);
        return $this;
    }

    public function options(callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, 'options', $handler);
        return $this;
    }

    public function any(callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, '*', $handler);
        return $this;
    }

    public function on(string|array $methods, callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, $methods, $handler);
        return $this;
    }

    public function apply($middlewares)
    {
        if (!is_array($middlewares)) {
            $middlewares = func_get_args();
        }
        Router::getInstance()->addMiddlewares($middlewares, $this->route, $this->method);
    }
}