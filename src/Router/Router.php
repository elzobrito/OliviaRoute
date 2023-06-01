<?php

namespace OliviaRouter\Router;

use OliviaRouter\Request\Request;

class Router
{
    private $routes = [];
    private $middlewares = [];

    public function get($pattern, $controller_method)
    {
        $this->addRoute('GET', $pattern, $controller_method);
    }

    public function post($pattern, $controller_method)
    {
        $this->addRoute('POST', $pattern, $controller_method);
    }

    public function addRoute($http_method, $pattern, $controller_method)
    {
        $pattern = $this->preparePattern($pattern);
        $this->routes[] = new Route($http_method, $pattern, $controller_method);
    }

    public function addMiddleware($middleware)
    {
        $this->middlewares[] = $middleware;
    }

    public function execute(Request $request)
    {
        foreach ($this->routes as $route) {
            if ($route->matches($request)) {
                $this->processMiddleware();
                $this->invokeControllerMethod($route->getControllerMethod(), $route->getParams());
                return;
            }
        }

        $this->handle404();
    }

    private function processMiddleware()
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->handle();
        }
    }

    private function invokeControllerMethod($controller_method, $params)
    {
        [$controllerClass, $methodName] = explode('@', $controller_method);

        $controllerInstance = new $controllerClass();
        $controllerInstance->{$methodName}(...$params);
    }

    private function handle404()
    {
        http_response_code(404);
        // Handle the 404 error accordingly (e.g., show a custom error page)
        echo "404 - Not Found";
        exit;
    }

    private function preparePattern($pattern)
    {
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        $pattern = preg_replace('/\{(.+?)\}/', '(?<$1>[^\/]+)', $pattern);
        return $pattern;
    }
}
