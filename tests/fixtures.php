<?php

namespace Tests\Support;

class Probe
{
    public static array $events = [];

    public static function reset(): void
    {
        self::$events = [];
        unset($GLOBALS['OLIVIA_ROUTER_404'], $_COOKIE, $_SESSION, $_GET, $_POST, $_SERVER);
        $_COOKIE = [];
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
    }

    public static function push(string $key, $value): void
    {
        self::$events[$key] = $value;
    }
}

namespace App\Controller;

use Tests\Support\Probe;

class Users
{
    public function show(array $params): void
    {
        Probe::push('handler', ['action' => 'show', 'params' => $params]);
    }

    public function store(array $params): void
    {
        Probe::push('handler', ['action' => 'store', 'params' => $params]);
    }
}

class Dashboard
{
    public function index(array $params): void
    {
        Probe::push('handler', ['action' => 'index', 'params' => $params]);
    }
}

namespace App\Middleware;

use Tests\Support\Probe;

class Auth
{
    public function handle(): void
    {
        Probe::push('middleware', 'auth');
    }
}
