<?php

namespace OliviaRouter;

class Router
{
    private array $route = [];
    private array $clauses = [];
    private RouterConfig $config;
    private Trie $trie;

    public function __construct(?RouterConfig $config = null)
    {
        $this->route = [];
        $this->clauses = [];
        $this->config = $config ?? RouterConfig::fromSession();
        $this->trie = new Trie();
    }

    public function get($pattern, $controller_method)
    {
        $this->route('get', $pattern, $controller_method, $this->clauses['middleware'] ?? null);
    }

    public function post($pattern, $controller_method)
    {
        $this->route('post', $pattern, $controller_method, $this->clauses['middleware'] ?? null);
    }

    public function put($pattern, $controller_method)
    {
        $this->route('put', $pattern, $controller_method, $this->clauses['middleware'] ?? null);
    }

    public function delete($pattern, $controller_method)
    {
        $this->route('delete', $pattern, $controller_method, $this->clauses['middleware'] ?? null);
    }

    public function patch($pattern, $controller_method)
    {
        $this->route('patch', $pattern, $controller_method, $this->clauses['middleware'] ?? null);
    }

    public function route($http_method, $pattern, $controller_method, $middleware)
    {
        $this->route[] = new Route(
            $http_method,
            $this->normalizePattern($pattern),
            $controller_method,
            $middleware
        );
    }

    public function execute($request_data)
    {
        $routerDispatcher = new RouterDispatcher($this->route, $this->config, $this->trie, $this->clauses);
        $routerDispatcher->dispatch($request_data);
    }

    public function __call($name, $arguments)
    {
        $clause = $arguments[0] ?? null;
        if (count($arguments) > 1) {
            $clause = $arguments;
        }
        $this->clauses[strtolower($name)] = $clause;
        return $this;
    }

    public function getRoutes(): array
    {
        return $this->route;
    }

    public function getConfig(): RouterConfig
    {
        return $this->config;
    }

    private function normalizePattern(string $pattern): string
    {
        $basePath = trim($this->config->getBasePath(), '/');
        $pattern = '/' . ltrim($pattern, '/');

        if ($basePath === '') {
            return $pattern;
        }

        return '/' . $basePath . $pattern;
    }
}
