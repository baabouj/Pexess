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
        is_array($this->method) ? $this->method[] = 'get' : $this->method = ['get'];
        return $this;
    }

    public function post(callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, 'post', $handler);
        is_array($this->method) ? $this->method[] = 'post' : $this->method = ['post'];
        return $this;
    }

    public function put(callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, 'put', $handler);
        is_array($this->method) ? $this->method[] = 'put' : $this->method = ['put'];
        return $this;
    }

    public function patch(callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, 'patch', $handler);
        is_array($this->method) ? $this->method[] = 'patch' : $this->method = ['patch'];
        return $this;
    }

    public function delete(callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, 'delete', $handler);
        is_array($this->method) ? $this->method[] = 'delete' : $this->method = ['delete'];
        return $this;
    }

    public function options(callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, 'options', $handler);
        is_array($this->method) ? $this->method[] = 'delete' : $this->method = ['delete'];
        return $this;
    }

    public function any(callable|array|string $handler): static
    {
        Router::getInstance()->addRoute($this->route, '*', $handler);
        is_array($this->method) ? $this->method[] = '*' : $this->method = ['*'];
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

        $router = Router::getInstance();
        if (is_array($this->method)) {
            foreach ($this->method as $method) {
                $handler = array_pop($router->routes[$this->route][$method]);
                $router->routes[$this->route][$method] = [...$router->middlewares, ...$middlewares, $handler];
            }
            return;
        }

        $handler = array_pop($router->routes[$this->route][$this->method]);
        $router->routes[$this->route][$this->method] = [...$router->middlewares, ...$middlewares, $handler];
    }
}