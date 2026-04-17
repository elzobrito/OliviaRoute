<?php

namespace OliviaRouter;

class RouterConfig
{
    private const COOKIE_APP_NAMESPACE = 'OLIVIA_APP_NAMESPACE';
    private const COOKIE_CONTROLLER_FOLDER = 'OLIVIA_CONTROLLER_FOLDER';
    private const COOKIE_MIDDLEWARE_FOLDER = 'OLIVIA_MIDDLEWARE_FOLDER';
    private const COOKIE_BASE_PATH = 'OLIVIA_BASE_PATH';
    private const COOKIE_CSRF_ENABLED = 'OLIVIA_CSRF';
    private const COOKIE_CSRF_TOKEN = 'OLIVIA_CSRF_TOKEN';

    private string $appNamespace;
    private string $controllerFolder;
    private string $middlewareFolder;
    private string $basePath;
    private bool $csrfEnabled;
    private string $csrfToken;

    public function __construct(
        string $appNamespace = 'App',
        string $controllerFolder = 'Controller',
        string $middlewareFolder = 'Middleware',
        string $basePath = '',
        bool $csrfEnabled = false,
        string $csrfToken = ''
    ) {
        $this->appNamespace = trim($appNamespace, '\\') ?: 'App';
        $this->controllerFolder = trim($controllerFolder, '\\') ?: 'Controller';
        $this->middlewareFolder = trim($middlewareFolder, '\\') ?: 'Middleware';
        $this->basePath = trim($basePath, '/');
        $this->csrfEnabled = $csrfEnabled;
        $this->csrfToken = $csrfToken;
    }

    public static function fromGlobals(?array $cookies = null, ?array $session = null): self
    {
        return self::fromContextStore(
            new FallbackContextStore(
                new CookieContextStore($cookies),
                new SessionContextStore($session)
            )
        );
    }

    public static function fromRequestData(array $requestData, ?array $session = null): self
    {
        return self::fromGlobals(
            $requestData['COOKIE'] ?? $_COOKIE ?? [],
            $session
        );
    }

    public static function fromRequest(Request $request, ?array $session = null): self
    {
        return self::fromContextStore(
            new FallbackContextStore(
                new CookieContextStore($request->getCookieData()),
                new SessionContextStore($session)
            )
        );
    }

    public static function fromSession(): self
    {
        return self::fromGlobals();
    }

    public static function fromContextStore(ContextStoreInterface $store): self
    {
        return new self(
            self::readString($store, self::COOKIE_APP_NAMESPACE, 'App_folder', 'App'),
            self::readString($store, self::COOKIE_CONTROLLER_FOLDER, 'Controller_folder', 'Controller'),
            self::readString($store, self::COOKIE_MIDDLEWARE_FOLDER, 'Middleware_folder', 'Middleware'),
            self::readString($store, self::COOKIE_BASE_PATH, 'BASENAME', ''),
            self::readBool($store, self::COOKIE_CSRF_ENABLED, 'CSRF', false),
            self::readString($store, self::COOKIE_CSRF_TOKEN, 'UUID', '')
        );
    }

    private static function readString(
        ContextStoreInterface $store,
        string $cookieKey,
        string $legacyKey,
        string $default
    ): string {
        $value = $store->get($cookieKey);
        if ($value !== null && $value !== '') {
            return (string) $value;
        }

        $value = $store->get($legacyKey);
        if ($value !== null && $value !== '') {
            return (string) $value;
        }

        return $default;
    }

    private static function readBool(
        ContextStoreInterface $store,
        string $cookieKey,
        string $legacyKey,
        bool $default
    ): bool {
        if ($store->has($cookieKey)) {
            return filter_var($store->get($cookieKey), FILTER_VALIDATE_BOOLEAN);
        }

        if ($store->has($legacyKey)) {
            $value = $store->get($legacyKey);
            return $value === true || $value === 1 || $value === '1' || $value === 'true';
        }

        return $default;
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

    public function getCsrfToken(): string
    {
        return $this->csrfToken;
    }
}
