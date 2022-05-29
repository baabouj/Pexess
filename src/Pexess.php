<?php

namespace Pexess;

use Pexess\Container\Container;
use Pexess\Exceptions\HttpException;
use Pexess\Exceptions\InternalServerErrorException;
use Pexess\Exceptions\MethodNotAllowedException;
use Pexess\Exceptions\NotFoundException;
use Pexess\Http\Request;
use Pexess\Http\Response;
use Pexess\Router\Router;

class Pexess
{
    private Router $router;

    private Request $request;
    private Response $response;

    private Container $container;

    private static ?Pexess $Application = null;

    protected \Closure|array|null $stack = null;

    public static array $routeParams;

    private function __construct()
    {
        $this->router = Router::getInstance();
        $this->request = new Request();
        $this->response = new Response();
        $this->container = new Container();

        $this->container->set(Request::class, fn() => $this->request);
        $this->container->set(Response::class, fn() => $this->response);
    }

    public static function Application(): Pexess
    {
        if (!self::$Application) {
            self::$Application = new Pexess();
        }
        return self::$Application;
    }

    private function applyMiddlewares()
    {
        $this->applyRouteMiddlewares();
        $this->applyGlobalMiddlewares();
    }

    private function applyGlobalMiddlewares()
    {
        foreach (array_reverse($this->router->middlewares["*"]["*"] ?? []) as $middleware) {
            $next = $this->stack;
            $this->stack = function () use ($next, $middleware) {
                if (is_string($middleware)) $middleware = [$this->container->get($middleware), "handler"];
                return call_user_func($middleware, $this->request, $this->response, $next);
            };
        }
    }

    private function applyRouteMiddlewares()
    {
        $middlewares = array_reverse($this->router->middlewares[$this->request->url()][$this->request->method()] ?? $this->router->middlewares[$this->request->url()]['*'] ?? []);
        foreach ($middlewares as $middleware) {
            $next = $this->stack;
            $this->stack = function () use ($next, $middleware) {
                if (is_string($middleware)) $middleware = [$this->container->get($middleware), "handler"];
                return call_user_func($middleware, $this->request, $this->response, $next);
            };
        }
    }

    private function getRouteHandler()
    {
        $route = $this->router->routes[$this->request->url()] ?? false;
        if ($route) {
            $handler = $route[$this->request->method()] ?? $route['*'] ?? false;

            if (!$handler) throw new MethodNotAllowedException();

            return $handler;
        }

        foreach ($this->router->routes as $route => $actions) {
            $routeUrl = $route;
            preg_match_all('/{[^}]+}/', $route, $keys);
            $route = preg_replace('/{[^}]+}/', '(.+)', $route);
            if (preg_match("%^{$route}$%", $this->request->url(), $matches)) {
                unset($matches[0]);
                foreach (array_values($matches) as $index => $param) {
                    if (str_contains($param, '/')) {
                        $params = [];
                        break;
                    }
                    $params[trim($keys[0][$index], '{}')] = $param;
                }
                if (empty($params)) continue;
                self::$routeParams = $params;
                $handler = $actions[$this->request->method()] ?? $actions['*'];
                if (!$handler) throw new MethodNotAllowedException();
                $middleware = $this->router->middlewares[$routeUrl] ?? false;
                if ($middleware) {
                    $this->router->middlewares[$this->request->url()] = $middleware;
                }
                return $handler;
            }
        }
        return false;
    }

    private function resolve(): void
    {
        $handler = $this->getRouteHandler();

        if (!$handler) {
            throw new NotFoundException();
        }

        if (is_array($handler)) {
            $handler = fn() => $this->container->make($this->container->get($handler[0]), $handler[1]);
        } else {
            $handler = fn() => $this->container->call($handler);
        }

        $this->stack = $handler;
        $this->applyMiddlewares();
        call_user_func($this->stack);
    }

    public function init(): void
    {
        try {
            $this->resolve();
        } catch (\Exception $e) {
            if (!($e instanceof HttpException)) {
                $e = new InternalServerErrorException();
            }
            $e->handle($this->response);
        }
    }

}