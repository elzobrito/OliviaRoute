<?php

namespace OliviaRouter;

class Router
{
    private $route;
    private $clauses;

    public function __construct()
    {
        $this->route = [];
        $this->clauses = [];
    }

    public function get($pattern, $controller_method)
    {
        $this->route('get', $pattern, $controller_method, isset($this->clauses['middleware']) ? $this->clauses['middleware'] : null);
    }

    public function post($pattern, $controller_method)
    {
        if ($this->isCsrfEnabled()) {
            if ($this->requestPost('_token') === $_SESSION['UUID']) {
                $this->route('post', $pattern, $controller_method, isset($this->clauses['middleware']) ? $this->clauses['middleware'] : null);
            }
        } else {
            $this->route('post', $pattern, $controller_method, isset($this->clauses['middleware']) ? $this->clauses['middleware'] : null);
        }
    }

    public function route($http_method, $pattern, $controller_method, $middleware)
    {
        $pattern = $this->routeToRegex('/' . $_SESSION['BASENAME'] . $pattern);

        $handler = [
            'http_method' => $http_method,
            'url_pattern' => $pattern,
            'handler' => $controller_method,
            'middleware' => $middleware
        ];

        $this->route[] = $handler;
    }

    public function execute($request_data)
    {
        $routerDispatcher = new RouterDispatcher($this->route, $this->clauses);
        $routerDispatcher->dispatch($request_data);
    }

    private function isCsrfEnabled()
    {
        return isset($_SESSION['CSRF']) && $_SESSION['CSRF'] === true;
    }

    public function __call($name, $arguments)
    {
        $clause = $arguments[0];
        if (count($arguments) > 1) {
            $clause = $arguments;
        }
        $this->clauses[strtolower($name)] = $clause;
        return $this;
    }

    private function routeToRegex($pattern)
    {
        $pattern = preg_replace('/\//', '\\/', $pattern);
        $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-zA-Z0-9-]+)', $pattern);
        $pattern = '/^' . $pattern . '$/';
        return $pattern;
    }
}
