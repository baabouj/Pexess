<?php

namespace Pexess\Router;

use Pexess\Pexess;

class Route
{

    public function __construct(
        private string $route,
        private Router $router
    )
    {
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function get($handler): Route
    {
        $this->router->routes[$this->route]['get'] = $handler;
        return $this;
    }

    public function post($handler): Route
    {
        $this->router->routes[$this->route]['post'] = $handler;
        return $this;
    }

    public function put($handler): Route
    {
        $this->router->routes[$this->route]['put'] = $handler;
        return $this;
    }

    public function patch($handler): Route
    {
        $this->router->routes[$this->route]['patch'] = $handler;
        return $this;
    }

    public function delete($handler): Route
    {
        $this->router->routes[$this->route]['delete'] = $handler;
        return $this;
    }

    public function apply(callable|string ...$middlewares)
    {
        foreach ($middlewares as $middleware) {
            $this->router->middlewares[$this->route][] = $middleware;
        }
    }
}