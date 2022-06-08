<?php

namespace Pexess;

use Closure;
use Pexess\Container\Container;
use Pexess\Exceptions\HttpException;
use Pexess\Exceptions\InternalServerErrorException;
use Pexess\Exceptions\MethodNotAllowedException;
use Pexess\Exceptions\NotFoundException;
use Pexess\Helpers\StatusCodes;
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

    protected Closure|array|null $stack = null;

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

    private function compose(array $stack)
    {
        foreach (array_reverse($stack) as $middleware) {
            $next = $this->stack;
            $this->stack = function () use ($next, $middleware) {
                if (is_string($middleware)) $middleware = [$this->container->get($middleware), "handler"];
                return call_user_func($middleware, $this->request, $this->response, $next);
            };
        }
    }

    private function getRouteHandlers()
    {
        $route = $this->router->routes[$this->request->url()] ?? false;

        if ($route) {
            $handler = $route[$this->request->method()] ?? $route['*'] ?? false;

            $this->response->throwIf(!$handler, MethodNotAllowedException::class);

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
                $this->response->throwIf(!$handler, MethodNotAllowedException::class);
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
        $handlers = $this->getRouteHandlers();

        $this->response->throwIf(!$handlers, NotFoundException::class);

        $handler = array_pop($handlers);

        if (is_array($handler)) {
            $handler = fn() => $this->container->make($this->container->get($handler[0]), $handler[1]);
        } else {
            $handler = fn() => $this->container->call($handler);
        }

        $handlers[] = $handler;

        $this->compose($handlers);

        call_user_func($this->stack);
    }

    public function init(): void
    {
        try {
            $this->resolve();
        } catch (\Exception $e) {
            if ($this->request->method() == "options") {
                $this->response->status(StatusCodes::NO_CONTENT)->end();
            }
            if (!($e instanceof HttpException)) {
                $e = new InternalServerErrorException();
            }
            $e->handle($this->response);
        }
    }

}