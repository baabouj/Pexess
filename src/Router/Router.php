<?php

namespace Pexess\Router;

use Closure;

class Router
{
    private static self $instance;

    public array $routes = [];
    public array $middlewares = [];

    protected string $prefix = '';
    protected string $controller = '';

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new Router();
        }
        return self::$instance;
    }

    public function addRoute(string $path, string|array $method, callable|array|string $handler): Route
    {
        $router = self::getInstance();

        if (!empty($this->controller) && is_string($handler)) {
            $handler = [$this->controller, $handler];
        }

        if (is_array($method)) {
            foreach ($method as $m) {
                $router->routes[$path][strtolower($m)] = [...$this->middlewares, $handler];
            }
        } else {
            $router->routes[$path][$method] = [...$this->middlewares, $handler];
        }

        return new Route($path, $method);
    }

    public static function get(string $path, callable|array|string $handler): Route
    {
        return self::on('get', $path, $handler);
    }

    public static function post(string $path, callable|array|string $handler): Route
    {
        return self::on('post', $path, $handler);
    }

    public static function put(string $path, callable|array|string $handler): Route
    {
        return self::on('put', $path, $handler);
    }

    public static function patch(string $path, callable|array|string $handler): Route
    {
        return self::on('patch', $path, $handler);
    }

    public static function delete(string $path, callable|array|string $handler): Route
    {
        return self::on('delete', $path, $handler);
    }

    public static function options(string $path, callable|array|string $handler): Route
    {
        return self::on('options', $path, $handler);
    }

    public static function any(string $path, callable|array|string $handler): Route
    {
        return self::on('*', $path, $handler);
    }

    public static function on(string|array $method, string $path, callable|array|string $handler): Route
    {
        $instance = self::getInstance();
        if (!empty($instance->prefix)) {
            $path = rtrim($instance->prefix . $path, '/');
        }
        return self::getInstance()->addRoute($path, $method, $handler);
    }

    public static function route(string $path): Route
    {
        $instance = self::getInstance();
        if (!empty($instance->prefix)) {
            $path = rtrim($instance->prefix . $path, '/');
        }
        return new Route($path);
    }

    public static function prefix(string $prefix, Closure $group)
    {
        $instance = self::getInstance();
        $instance->prefix .= $prefix;
        call_user_func($group);
        $instance->prefix = substr($instance->prefix, 0, -strlen($prefix));
    }

    public static function controller(string $controller, Closure $group)
    {
        $instance = self::getInstance();
        $instance->controller = $controller;
        call_user_func($group);
        $instance->controller = '';
    }

    public static function apply($middlewares): void
    {
        if (!is_array($middlewares)) {
            $middlewares = func_get_args();
        }

        self::getInstance()->addMiddlewares($middlewares);
    }

    public function addMiddlewares(array $middlewares): void
    {
        $instance = self::getInstance();
        array_push($instance->middlewares, ...$middlewares);
    }
}