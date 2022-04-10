<?php

namespace Pexess\Router;

class Router
{
    public array $routes = [];
    public array $middlewares = [
        "*" => [] // Global middlewares
    ];

    protected \Closure|array|null $stack = null;

    public function get(string $url, \Closure|array $callback)
    {
        $this->routes[$url]['get'] = $callback;
    }

    public function post(string $url, \Closure|array $callback)
    {
        $this->routes[$url]['post'] = $callback;
    }

    public function put(string $url, \Closure|array $callback)
    {
        $this->routes[$url]['put'] = $callback;
    }

    public function delete(string $url, \Closure|array $callback)
    {
        $this->routes[$url]['delete'] = $callback;
    }

    public function route(string $route): Route
    {
        return new Route($route, $this);
    }

    public function group(string $route, Router $router)
    {
        foreach ($router->routes as $path => $handler) {
            $this->routes[rtrim($route . $path, '/')] = $handler;
        }
        foreach ($router->middlewares as $path => $middlewares) {
            $this->middlewares[rtrim($route . $path, '/')] = [...$router->middlewares["*"], ...$middlewares];
        }
    }

    public function apply(callable|string ...$middlewares)
    {
        array_push($this->middlewares["*"], ...$middlewares);
    }
}