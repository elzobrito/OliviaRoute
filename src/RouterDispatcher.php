<?php

namespace OliviaRouter;

class RouterDispatcher
{
    private array $routes;
    private RouterConfig $config;
    private Trie $trie;
    private array $clauses;
    private bool $matched = false;

    public function __construct(array $routes, $configOrClauses = [], ?Trie $trie = null, array $clauses = [])
    {
        $this->routes = $routes;
        $this->trie = $trie ?? new Trie();

        if ($configOrClauses instanceof RouterConfig) {
            $this->config = $configOrClauses;
            $this->clauses = $clauses;
            return;
        }

        $this->config = RouterConfig::fromGlobals();
        $this->clauses = is_array($configOrClauses) ? $configOrClauses : [];
    }

    public function dispatch($request_data)
    {
        $request = $request_data instanceof Request
            ? $request_data
            : Request::fromArray((array) $request_data);

        $this->matched = false;
        $this->publishNotFoundState(true);

        foreach ($this->routes as $route) {
            $route = $this->normalizeRoute($route);

            if (!$route->matches($request, $this->trie)) {
                continue;
            }

            $this->executeMiddlewares($route->getMiddleware());
            $this->validateCsrf($route, $request);
            $this->executeHandler($route->getControllerMethod(), $route->getParams(), $request);

            $this->matched = true;
            $this->publishNotFoundState(false);
            return;
        }
    }

    private function validateCsrf(Route $route, Request $request): void
    {
        if (!$this->config->isCsrfEnabled() || !in_array($route->getHttpMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $token = $request->post('_token');
        if ($token !== $this->config->getCsrfToken()) {
            throw new \RuntimeException('CSRF token inválido ou ausente.');
        }
    }

    public function hasMatched(): bool
    {
        return $this->matched;
    }

    private function executeMiddlewares($middlewares): void
    {
        if ($middlewares === null) {
            return;
        }

        $middlewares = is_array($middlewares) ? $middlewares : [$middlewares];

        foreach ($middlewares as $middleware) {
            $middleware = strpos($middleware, '#') === false ? $middleware . '#handle' : $middleware;
            $callMiddleware = $this->callMiddlewareClass($middleware);
            $middlewareInstance = MiddlewareFactory::createMiddleware($callMiddleware['middleware']);

            if (!method_exists($middlewareInstance, $callMiddleware['action'])) {
                throw new \RuntimeException(
                    "Método {$callMiddleware['action']} não encontrado em {$callMiddleware['middleware']}."
                );
            }

            $middlewareInstance->{$callMiddleware['action']}();
        }
    }

    private function executeHandler(string $handler, array $params, Request $request): void
    {
        $callHandler = $this->callHandlerClass($handler);
        $handlerInstance = ControllerFactory::createController($callHandler['controller']);

        if (!method_exists($handlerInstance, $callHandler['action'])) {
            throw new \RuntimeException(
                "Método {$callHandler['action']} não encontrado em {$callHandler['controller']}."
            );
        }

        $format = $request->getContentType() ?? 'text/html';
        $mimeType = explode(';', $format)[0];
        $params['format'] = explode('/', $mimeType)[1] ?? 'html';
        $params['method'] = $request->getMethod();

        $handlerInstance->{$callHandler['action']}($params);
    }

    private function callMiddlewareClass(string $str): array
    {
        return $this->resolveCallable($str, $this->config->getMiddlewareFolder());
    }

    private function callHandlerClass(string $str): array
    {
        return $this->resolveCallable($str, $this->config->getControllerFolder());
    }

    private function resolveCallable(string $str, string $defaultFolder): array
    {
        $callables = explode('#', $str);
        $class = trim($callables[0], '\\');
        $action = $callables[1] ?? 'handle';

        if (strpos($class, $this->config->getAppNamespace() . '\\') === 0) {
            $resolvedClass = str_replace('/', '\\', $class);
            return ['controller' => $resolvedClass, 'middleware' => $resolvedClass, 'action' => $action];
        }

        $parts = array_map('ucfirst', array_filter(explode('/', str_replace('\\', '/', $class)), 'strlen'));
        $resolvedClass = $this->config->getAppNamespace()
            . '\\' . $defaultFolder
            . '\\' . implode('\\', $parts);

        return ['controller' => $resolvedClass, 'middleware' => $resolvedClass, 'action' => $action];
    }

    private function normalizeRoute($route): Route
    {
        if ($route instanceof Route) {
            return $route;
        }

        if (is_array($route)) {
            return Route::fromLegacyArray($route);
        }

        throw new \InvalidArgumentException('Formato de rota inválido.');
    }

    private function publishNotFoundState(bool $notFound): void
    {
        $GLOBALS['OLIVIA_ROUTER_404'] = $notFound;

        if (isset($_SESSION) && is_array($_SESSION)) {
            $_SESSION['e404'] = $notFound;
        }
    }
}
