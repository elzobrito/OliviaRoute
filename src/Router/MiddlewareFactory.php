<?php

namespace OliviaRouter;

class MiddlewareFactory
{
    public static function createMiddleware(string $middlewareName): RequestHandler
    {
        $callables = explode('#', $middlewareName);
        $controllerParts = array_map('ucfirst', explode('/', $callables[0]));
        $controllerClass = implode('\\', $controllerParts);
        $controllerInstance = new $controllerClass();
        return $controllerInstance;
    }
}
