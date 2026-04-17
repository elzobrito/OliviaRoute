<?php

namespace Tests;

use OliviaRouter\Request;
use OliviaRouter\Router;
use Tests\Support\Probe;

class RouterTest extends TestCase
{
    public function setUp(): void
    {
        Probe::reset();
    }

    public function test_dispatches_get_route_and_extracts_named_param(): void
    {
        $router = new Router();
        $router->get('/users/{id}', 'users#show');

        $router->execute([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/users/42?tab=profile',
            'CONTENT_TYPE' => 'text/html',
        ]);

        $handler = Probe::$events['handler'] ?? null;
        $this->assertSame('show', $handler['action'] ?? null);
        $this->assertSame('42', $handler['params']['id'] ?? null);
        $this->assertSame('GET', $handler['params']['method'] ?? null);
        $this->assertSame('html', $handler['params']['format'] ?? null);
        $this->assertFalse($GLOBALS['OLIVIA_ROUTER_404'] ?? true);
    }

    public function test_executes_middleware_before_handler(): void
    {
        $router = new Router();
        $router->middleware('auth')->get('/dashboard', 'dashboard#index');

        $router->execute([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/dashboard',
            'CONTENT_TYPE' => 'text/html',
        ]);

        $this->assertSame('auth', Probe::$events['middleware'] ?? null);
        $this->assertSame('index', Probe::$events['handler']['action'] ?? null);
    }

    public function test_validates_csrf_using_cookie_context(): void
    {
        $router = new Router();
        $router->post('/users', 'users#store');

        $router->execute([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/users',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'POST' => ['_token' => 'cookie-token'],
            'COOKIE' => [
                'OLIVIA_APP_NAMESPACE' => 'App',
                'OLIVIA_CONTROLLER_FOLDER' => 'Controller',
                'OLIVIA_MIDDLEWARE_FOLDER' => 'Middleware',
                'OLIVIA_CSRF' => 'true',
                'OLIVIA_CSRF_TOKEN' => 'cookie-token',
            ],
        ]);

        $this->assertSame('store', Probe::$events['handler']['action'] ?? null);
    }

    public function test_rejects_invalid_csrf_token(): void
    {
        $router = new Router();
        $router->post('/users', 'users#store');

        $this->expectException(\RuntimeException::class, function () use ($router): void {
            $router->execute([
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/users',
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
                'POST' => ['_token' => 'wrong-token'],
                'COOKIE' => [
                    'OLIVIA_APP_NAMESPACE' => 'App',
                    'OLIVIA_CONTROLLER_FOLDER' => 'Controller',
                    'OLIVIA_MIDDLEWARE_FOLDER' => 'Middleware',
                    'OLIVIA_CSRF' => 'true',
                    'OLIVIA_CSRF_TOKEN' => 'expected-token',
                ],
            ]);
        }, 'CSRF token inválido');
    }

    public function test_uses_legacy_session_fallback_for_csrf(): void
    {
        $_SESSION['App_folder'] = 'App';
        $_SESSION['Controller_folder'] = 'Controller';
        $_SESSION['Middleware_folder'] = 'Middleware';
        $_SESSION['CSRF'] = true;
        $_SESSION['UUID'] = 'legacy-token';

        $router = new Router();
        $router->post('/users', 'users#store');

        $router->execute([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/users',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'POST' => ['_token' => 'legacy-token'],
        ]);

        $this->assertSame('store', Probe::$events['handler']['action'] ?? null);
    }

    public function test_marks_not_found_when_no_route_matches(): void
    {
        $router = new Router();
        $router->get('/users/{id}', 'users#show');

        $router->execute([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/missing',
            'CONTENT_TYPE' => 'text/html',
        ]);

        $this->assertTrue($GLOBALS['OLIVIA_ROUTER_404'] ?? false);
        $this->assertFalse(isset(Probe::$events['handler']), 'Handler should not run for missing routes');
    }

    public function test_throws_clear_error_for_missing_controller_method(): void
    {
        $router = new Router();
        $router->get('/users/{id}', 'users#missingMethod');

        $this->expectException(\RuntimeException::class, function () use ($router): void {
            $router->execute([
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/users/42',
                'CONTENT_TYPE' => 'text/html',
            ]);
        }, 'Método missingMethod');
    }

    public function test_request_object_path_also_works(): void
    {
        $_COOKIE['OLIVIA_APP_NAMESPACE'] = 'App';
        $_COOKIE['OLIVIA_CONTROLLER_FOLDER'] = 'Controller';
        $_COOKIE['OLIVIA_MIDDLEWARE_FOLDER'] = 'Middleware';

        $router = new Router();
        $router->get('/users/{id}', 'users#show');

        $request = new Request('GET', '/users/55', [], [], $_COOKIE, [], 'text/html');
        $router->execute($request);

        $this->assertSame('55', Probe::$events['handler']['params']['id'] ?? null);
    }
}
