<?php

namespace Pexess\Router;

use Pexess\Http\Request;
use Pexess\Http\Response;

class Router
{
    public array $routes = [];
    public array $middlewares = [
        "*"=>[] // Global middlewares
    ];

    protected ?\Closure $stack = null;

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
        $this->routes[$url]['post'] = $callback;
    }

    public function delete(string $url, \Closure|array $callback)
    {
        $this->routes[$url]['post'] = $callback;
    }

    public function route(string $route) : Route
    {
        return new Route($route);
    }

    public function group(string $route,Router $router)
    {
        foreach ($router->routes as $path => $handler) {
            $this->routes[rtrim($route . $path, '/')] = $handler;
            $this->middlewares[rtrim($route . $path, '/')] = [...$router->middlewares["*"]];
        }
    }

    public function apply(callable ...$middlewares)
    {
         array_push($this->middlewares["*"],...$middlewares);
    }
}