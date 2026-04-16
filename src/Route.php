<?php

namespace OliviaRouter;

class Route
{
    private string $httpMethod;
    private string $pattern;
    private string $controllerMethod;
    private $middleware;
    private array $params = [];
    private bool $compiledPattern;

    public function __construct(
        string $httpMethod,
        string $pattern,
        string $controllerMethod,
        $middleware = null,
        bool $compiledPattern = false
    ) {
        $this->httpMethod = strtoupper($httpMethod);
        $this->pattern = $pattern;
        $this->controllerMethod = $controllerMethod;
        $this->middleware = $middleware;
        $this->compiledPattern = $compiledPattern;
    }

    public static function fromLegacyArray(array $route): self
    {
        if (isset($route['url_pattern'])) {
            return new self(
                $route['http_method'] ?? 'GET',
                $route['url_pattern'],
                $route['handler'] ?? '',
                $route['middleware'] ?? null,
                true
            );
        }

        return new self(
            $route['http_method'] ?? 'GET',
            $route['pattern'] ?? '/',
            $route['handler'] ?? '',
            $route['middleware'] ?? null
        );
    }

    public function matches(Request $request, Trie $trie): bool
    {
        if ($request->getMethod() !== $this->httpMethod) {
            return false;
        }

        $matches = $this->compiledPattern
            ? $trie->searchRegex($this->pattern, $request->getUri())
            : $trie->search($this->pattern, $request->getUri());

        if ($matches === false) {
            $this->params = [];
            return false;
        }

        $this->params = $matches;
        return true;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getControllerMethod(): string
    {
        return $this->controllerMethod;
    }

    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }
}
