<?php

namespace OliviaRouter;

class RouterConfig
{
    private string $appNamespace;
    private string $controllerFolder;
    private string $middlewareFolder;
    private string $basePath;
    private bool $csrfEnabled;

    public function __construct(
        string $appNamespace = 'App',
        string $controllerFolder = 'Controller',
        string $middlewareFolder = 'Middleware',
        string $basePath = '',
        bool $csrfEnabled = false
    ) {
        $this->appNamespace = trim($appNamespace, '\\') ?: 'App';
        $this->controllerFolder = trim($controllerFolder, '\\') ?: 'Controller';
        $this->middlewareFolder = trim($middlewareFolder, '\\') ?: 'Middleware';
        $this->basePath = trim($basePath, '/');
        $this->csrfEnabled = $csrfEnabled;
    }

    public static function fromSession(): self
    {
        return new self(
            $_SESSION['App_folder'] ?? 'App',
            $_SESSION['Controller_folder'] ?? 'Controller',
            $_SESSION['Middleware_folder'] ?? 'Middleware',
            $_SESSION['BASENAME'] ?? '',
            isset($_SESSION['CSRF']) && $_SESSION['CSRF'] === true
        );
    }

    public function getAppNamespace(): string
    {
        return $this->appNamespace;
    }

    public function getControllerFolder(): string
    {
        return $this->controllerFolder;
    }

    public function getMiddlewareFolder(): string
    {
        return $this->middlewareFolder;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function isCsrfEnabled(): bool
    {
        return $this->csrfEnabled;
    }
}
