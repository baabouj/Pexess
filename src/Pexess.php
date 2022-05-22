<?php

namespace Pexess;

use Pexess\Container\Container;
use Pexess\Exceptions\MethodNotAllowedException;
use Pexess\Exceptions\NotFoundException;
use Pexess\Http\Request;
use Pexess\Http\Response;
use Pexess\Router\Router;

class Pexess extends Router
{
    private Request $request;
    private Response $response;

    private Container $container;

    private static ?Pexess $Application = null;
    public static array $routeParams;

    private array $errorHandlers = [];

    private function __construct()
    {
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

    public static function Router(): Router
    {
        return new Router();
    }

    public function cors(array $cors)
    {
        if (array_key_exists("origin", $cors)) {
            $origin = $cors["origin"];
            if (is_array($origin)) {
                $http_origin = $_SERVER["HTTP_ORIGIN"];
                $origin = in_array($http_origin, $origin) ? $http_origin : $origin[0];
            }
            if (is_bool($origin)) {
                $origin = $origin ? "*" : "";
            }
            header("Access-Control-Allow-Origin: $origin");
        }
        if (array_key_exists("headers", $cors)) {
            $headers = $cors["headers"];
            if (is_bool($headers)) {
                $headers = $headers ? "*" : "";
            }
            if (is_array($headers)) $headers = implode(", ", $headers);
            header("Access-Control-Allow-Headers: " . $headers);
        }
        if (array_key_exists("methods", $cors)) {
            $methods = $cors["methods"];
            if (is_array($methods)) $methods = implode(", ", $methods);
            header("Access-Control-Allow-Methods: " . $methods);
        }
        if (array_key_exists("maxAge", $cors)) {
            $maxAge = $cors["maxAge"];
            header("Access-Control-Allow-MaxAge: $maxAge");
        }
    }

    private function applyMiddlewares()
    {
        $this->applyRouteMiddlewares();
        $this->applyGlobalMiddlewares();
    }

    private function applyGlobalMiddlewares()
    {
        foreach (array_reverse($this->middlewares["*"]) as $middleware) {
            $next = $this->stack;
            $this->stack = function () use ($next, $middleware) {
                if (is_string($middleware)) $middleware = [$this->container->get($middleware), "handler"];
                return call_user_func($middleware, $this->request, $this->response, $next);
            };
        }
    }

    private function applyRouteMiddlewares()
    {
        foreach (array_reverse($this->middlewares[$this->request->url()] ?? []) as $middleware) {
            $next = $this->stack;
            $this->stack = function () use ($next, $middleware) {
                if (is_string($middleware)) $middleware = [$this->container->get($middleware), "handler"];
                return call_user_func($middleware, $this->request, $this->response, $next);
            };
        }
    }

    private function getRouteHandler()
    {
        $route = $this->routes[$this->request->url()] ?? false;
        if ($route) {
            $handler = $route[$this->request->method()] ?? false;

            if (!$handler) throw new MethodNotAllowedException();

            return $handler;
        }

        foreach ($this->routes as $route => $actions) {
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
                $handler = $actions[$this->request->method()];
                if (!$handler) throw new MethodNotAllowedException();
                $middleware = $this->middlewares[$routeUrl] ?? false;
                if ($middleware) {
                    $this->middlewares[$this->request->url()] = $middleware;
                }
                return $handler;
            }
        }
        return false;
    }

    public function handle(int $error_code, callable $handler)
    {
        $this->errorHandlers[$error_code] = $handler;
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
        call_user_func($this->stack, $this->request, $this->response);

    }

    public function init(): void
    {
        try {
            $this->resolve();
        } catch (\Exception $e) {
            $code = $e->getCode();
            $message = $e->getMessage();
            $errorHandler = $this->errorHandlers[$code] ?? false;

            if (!$errorHandler) {
                if ($this->request->method() == "options") {
                    $code = 204;
                    $message = "";
                }
                $this->response->status($code)->end($message);
            }

            $this->response->status($code);
            call_user_func($errorHandler, $this->request, $this->response, $message);
        }
    }

}