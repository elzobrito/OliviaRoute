<?php

namespace OliviaRouter;

class Request
{
    private string $method;
    private string $uri;
    private array $post;
    private array $get;
    private array $server;
    private ?string $contentType;

    public function __construct(
        string $method,
        string $uri,
        array $post = [],
        array $get = [],
        array $server = [],
        ?string $contentType = null
    ) {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->post = $post;
        $this->get = $get;
        $this->server = $server;
        $this->contentType = $contentType;
    }

    public static function fromArray(array $requestData): self
    {
        return new self(
            $requestData['REQUEST_METHOD'] ?? 'GET',
            $requestData['REQUEST_URI'] ?? '/',
            $requestData['POST'] ?? $_POST ?? [],
            $requestData['GET'] ?? $_GET ?? [],
            $requestData,
            $requestData['CONTENT_TYPE'] ?? null
        );
    }

    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    public function get(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    public function hasPost(string $key): bool
    {
        return isset($this->post[$key]);
    }

    public function getPostData(): array
    {
        return $this->post;
    }

    public function getQueryData(): array
    {
        return $this->get;
    }

    public function getServerData(): array
    {
        return $this->server;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }
}
