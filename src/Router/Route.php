<?php

namespace Pexess\Router;

use Pexess\Pexess;

class Route
{
    public function __construct(
        private string $route
    )
    {
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function get($handler): Route
    {
        Pexess::$Application->routes[$this->route]['get'] = $handler;
        return $this;
    }

    public function post($handler): Route
    {
        Pexess::$Application->routes[$this->route]['post'] = $handler;
        return $this;
    }

    public function put($handler): Route
    {
        Pexess::$Application->routes[$this->route]['put'] = $handler;
        return $this;
    }

    public function patch($handler): Route
    {
        Pexess::$Application->routes[$this->route]['patch'] = $handler;
        return $this;
    }

    public function delete($handler): Route
    {
        Pexess::$Application->routes[$this->route]['delete'] = $handler;
        return $this;
    }

    public function apply(callable|string ...$middlewares)
    {
        foreach ($middlewares as $middleware) {
            Pexess::$Application->middlewares[$this->route][] = $middleware;
        }
    }
}