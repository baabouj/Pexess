<?php

namespace Pexess;

use Pexess\Http\Request;
use Pexess\Http\Response;
use Pexess\Router\Router;

class Pexess extends Router
{
    private Request $request;
    private Response $response;

    public static ?Pexess $Application = null;

    private function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    public static function Application() : Pexess
    {
        if (!self::$Application){
            self::$Application = new Pexess();
        }
        return self::$Application;
    }

    public static function Router() : Router
    {
        return new Router();
    }

    public function cors(array $cors)
    {
        if (array_key_exists("origin",$cors)){
            $origin = $cors["origin"];
            if (is_string($origin)) header("Access-Control-Allow-Origin: $origin");
        }
        if (array_key_exists("headers",$cors)){
            $headers = $cors["headers"];
            if (is_bool($headers)) header("Access-Control-Allow-Headers: ".$headers ? "*" : "");
            if (is_array($headers)) header("Access-Control-Allow-Headers: ".implode(", ",$headers));
        }
        if (array_key_exists("methods",$cors)){
            $methods = $cors["methods"];
            if (is_array($methods)) header("Access-Control-Allow-Methods: ".implode(", ",$methods));
        }
        if (array_key_exists("maxAge",$cors)){
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
            $this->stack = function (Request $req, Response $res) use ($next, $middleware) {
                return call_user_func($middleware,$req,$res,$next);
            };
        }
    }

    private function applyRouteMiddlewares()
    {
        foreach (array_reverse($this->middlewares[$this->request->url()] ?? []) as $middleware) {
            $next = $this->stack;
            $this->stack = function (Request $req, Response $res) use ($next, $middleware) {
                return call_user_func($middleware,$req,$res,$next);
            };
        }
    }

    public function resolve()
    {
        $handler = $this->routes[$this->request->url()][$this->request->method()] ?? false;

        if ($handler) {
            $this->stack = $handler;
            $this->applyMiddlewares();
            call_user_func($this->stack, $this->request, $this->response);
        } else $this->response->status(404)->send("<h1>Not Found</h1>");
    }

}